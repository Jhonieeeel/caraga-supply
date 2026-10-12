<?php

use App\Actions\Requisition\CreateRequestAction;
use App\Actions\RequisitionItem\AllocateStockToRequisitionAction;
use App\Actions\Stock\CreateStockAction;
use App\Actions\Supply\CreateSupplyAction;
use App\Domain\Gasu\Aggregates\RequisitionAggregate;
use App\Domain\Gasu\Aggregates\SupplyAggregate;
use App\Domain\Gasu\Exceptions\DuplicateStockLotException;
use App\Livewire\Pages\Afms\Components\RequestDetail;
use App\Livewire\Pages\Afms\Components\RequestRIS;
use App\Livewire\Pages\Afms\Components\RequestRpci;
use App\Livewire\Pages\Afms\Components\RequestRsmi;
use App\Livewire\Pages\Afms\Components\RequestTable;
use App\Livewire\Pages\Afms\RequisitionTable;
use App\Livewire\Pages\Afms\StockTable;
use App\Livewire\Pages\Afms\SupplyTable;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Stock;
use App\Models\Supply;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Afms\GenerateRpciService;
use App\Services\Afms\GenerateRsmiService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-02 09:00:00'));
});

function gasuRequester(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('create-requisition', 'web'));

    return $user;
}

function gasuApprover(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(
        Permission::findOrCreate('create-requisition', 'web'),
        Permission::findOrCreate('approve-requisition', 'web'),
    );

    return $user;
}

function gasuStockManager(): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::findByName('GASU'));

    return $user;
}

function gasuSupply(string $name = 'Bond Paper'): Supply
{
    return app(CreateSupplyAction::class)->handle(['name' => $name, 'category' => 'supplies', 'unit' => 'ream']);
}

function gasuLot(Supply $supply, int $quantity = 10, string $stockNumber = 'stock-001'): Stock
{
    return app(CreateStockAction::class)->handle([
        'supply_id' => $supply->id,
        'stock_number' => $stockNumber,
        'barcode' => 'BC-' . $stockNumber,
        'stock_location' => 'Main Warehouse',
        'price' => 100,
        'quantity' => $quantity,
    ]);
}

/**
 * @param  array<int, int>  $items  stock_id => qty
 */
function gasuRequisition(User $owner, array $items): Requisition
{
    test()->actingAs($owner);

    $requisition = app(CreateRequestAction::class)->handle([
        'user_id' => $owner->id,
        'requested_by' => $owner->id,
    ]);

    app(AllocateStockToRequisitionAction::class)->handle($requisition, $items);

    return $requisition->fresh();
}

function gasuComplete(Requisition $requisition): Requisition
{
    RequisitionAggregate::retrieve($requisition->uuid)->complete('ris/signed.pdf', null)->persist();

    return $requisition->fresh();
}

function gasuStoredEventCount(): int
{
    return DB::table('stored_events')->count();
}

// ---------------------------------------------------------------- #1

test('#1 signed RIS upload rejects a non-PDF file and stores nothing', function () {
    Storage::fake('public');

    $owner = gasuRequester();
    $requisition = gasuRequisition($owner, [gasuLot(gasuSupply())->id => 2]);

    Livewire::actingAs($owner)
        ->test(RequestRIS::class)
        ->call('currentData', $requisition->id)
        ->set('temporaryFile', UploadedFile::fake()->create('shell.php', 5, 'application/x-php'))
        ->call('updateRIS')
        ->assertHasErrors('temporaryFile');

    expect(Storage::disk('public')->allFiles())->toBeEmpty()
        ->and($requisition->fresh()->completed)->toBeFalsy();
});

test('#1 signed RIS upload is always stored with a .pdf extension', function () {
    Storage::fake('public');

    $owner = gasuRequester();
    $requisition = gasuRequisition($owner, [gasuLot(gasuSupply())->id => 2]);

    Livewire::actingAs($owner)
        ->test(RequestRIS::class)
        ->call('currentData', $requisition->id)
        ->set('temporaryFile', UploadedFile::fake()->create('signed.PDF', 20, 'application/pdf'))
        ->call('updateRIS')
        ->assertHasNoErrors();

    $requisition->refresh();

    expect($requisition->completed)->toBeTruthy()
        ->and($requisition->pdf)->toMatch('/^ris\/SIGNED_RIS_[^\/]+\.pdf$/');

    Storage::disk('public')->assertExists($requisition->pdf);

    // storeAs() copies out of Livewire's temp test disk; remove this test's temp upload
    collect(Storage::disk('tmp-for-tests')->files('livewire-tmp'))
        ->filter(fn (string $file) => str_contains($file, base64_encode('signed.PDF')))
        ->each(fn (string $file) => Storage::disk('tmp-for-tests')->delete($file));
});

// ---------------------------------------------------------------- #2

test('#2 another user cannot complete or generate the RIS of someone else\'s requisition', function () {
    Storage::fake('public');

    $owner = gasuRequester();
    $requisition = gasuRequisition($owner, [gasuLot(gasuSupply())->id => 2]);
    $stranger = gasuRequester();

    // Authorization runs before anything else (no upload needed to prove it).
    Livewire::actingAs($stranger)
        ->test(RequestRIS::class)
        ->call('currentData', $requisition->id)
        ->call('updateRIS')
        ->assertForbidden();

    Livewire::actingAs($stranger)
        ->test(RequestRIS::class)
        ->call('currentData', $requisition->id)
        ->call('getRIS')
        ->assertForbidden();

    expect($requisition->fresh()->completed)->toBeFalsy();
});

// ---------------------------------------------------------------- #3

test('#3 confirmed() re-authorizes instead of deleting any id the client sends', function () {
    $owner = gasuRequester();
    $requisition = gasuRequisition($owner, [gasuLot(gasuSupply())->id => 2]);

    Livewire::actingAs(gasuRequester())
        ->test(RequestTable::class)
        ->call('confirmed', ['message' => 'Request Deleted', 'id' => $requisition->id])
        ->assertForbidden();

    expect(Requisition::find($requisition->id))->not->toBeNull();
});

// ---------------------------------------------------------------- #4

test('#4 deleting a pending requisition returns its allocated stock through the aggregate', function () {
    $owner = gasuRequester();
    $stock = gasuLot(gasuSupply(), 10);
    $requisition = gasuRequisition($owner, [$stock->id => 4]);

    expect($stock->fresh()->quantity)->toBe(6);

    Livewire::actingAs($owner)
        ->test(RequestTable::class)
        ->call('confirmed', ['message' => 'Request Deleted', 'id' => $requisition->id])
        ->assertHasNoErrors();

    expect(Requisition::find($requisition->id))->toBeNull()
        ->and(RequisitionItem::where('requisition_id', $requisition->id)->count())->toBe(0)
        ->and($stock->fresh()->quantity)->toBe(10)
        ->and(SupplyAggregate::retrieve($stock->supply->uuid)->allocatedTo($requisition->uuid))->toBe([]);

    // The aggregate agrees: the full 10 can be requested again.
    $again = gasuRequisition(gasuRequester(), [$stock->id => 10]);
    expect($again->items()->sum('requested_qty'))->toBe(10)
        ->and($stock->fresh()->quantity)->toBe(0);
});

test('#4 deleting an already issued (completed) requisition does not return stock', function () {
    $owner = gasuRequester();
    $stock = gasuLot(gasuSupply(), 10);
    $requisition = gasuComplete(gasuRequisition($owner, [$stock->id => 4]));

    Livewire::actingAs(gasuApprover())
        ->test(RequestTable::class)
        ->call('confirmed', ['message' => 'Request Deleted', 'id' => $requisition->id]);

    expect(Requisition::find($requisition->id))->toBeNull()
        ->and($stock->fresh()->quantity)->toBe(6);
});

// ---------------------------------------------------------------- #5

test('#5 requisition items cannot be edited or deleted by another user', function () {
    $owner = gasuRequester();
    $stock = gasuLot(gasuSupply(), 10);
    $requisition = gasuRequisition($owner, [$stock->id => 4]);
    $item = $requisition->items()->first();

    Livewire::actingAs(gasuRequester())
        ->test(RequestDetail::class)
        ->call('editRequestItem', $item->id)
        ->assertForbidden();

    Livewire::actingAs(gasuRequester())
        ->test(RequestDetail::class)
        ->call('deleteRequisitionItem', $item->id)
        ->assertForbidden();

    expect($item->fresh()->requested_qty)->toBe(4)
        ->and($stock->fresh()->quantity)->toBe(6);
});

test('#5 item quantity must be a positive integer', function ($bad) {
    $owner = gasuRequester();
    $stock = gasuLot(gasuSupply(), 10);
    $requisition = gasuRequisition($owner, [$stock->id => 4]);
    $item = $requisition->items()->first();

    Livewire::actingAs($owner)
        ->test(RequestDetail::class)
        ->call('view', $requisition->id)
        ->call('editRequestItem', $item->id)
        ->set('itemForm.requested_qty', $bad)
        ->call('updateRequestItem')
        ->assertHasErrors('itemForm.requested_qty');

    expect($item->fresh()->requested_qty)->toBe(4)
        ->and($stock->fresh()->quantity)->toBe(6);
})->with(['-50', '0', '2.5', 'abc', '']);

test('#5 item quantity changes and item deletion adjust stock through the aggregate', function () {
    $owner = gasuRequester();
    $stock = gasuLot(gasuSupply(), 10);
    $requisition = gasuRequisition($owner, [$stock->id => 4]);
    $item = $requisition->items()->first();

    $component = Livewire::actingAs($owner)
        ->test(RequestDetail::class)
        ->call('view', $requisition->id)
        ->call('editRequestItem', $item->id)
        ->set('itemForm.requested_qty', 7)
        ->call('updateRequestItem')
        ->assertHasNoErrors();

    expect($item->fresh()->requested_qty)->toBe(7)
        ->and($stock->fresh()->quantity)->toBe(3);

    // more than available -> validation error, nothing changes
    $component->call('editRequestItem', $item->id)
        ->set('itemForm.requested_qty', 20)
        ->call('updateRequestItem')
        ->assertHasErrors('itemForm.requested_qty');

    expect($item->fresh()->requested_qty)->toBe(7)
        ->and($stock->fresh()->quantity)->toBe(3);

    $component->call('editRequestItem', $item->id)
        ->set('itemForm.requested_qty', 2)
        ->call('updateRequestItem')
        ->assertHasNoErrors();

    expect($item->fresh()->requested_qty)->toBe(2)
        ->and($stock->fresh()->quantity)->toBe(8);

    $component->call('deleteRequisitionItem', $item->id);

    expect(RequisitionItem::find($item->id))->toBeNull()
        ->and($stock->fresh()->quantity)->toBe(10)
        ->and(SupplyAggregate::retrieve($stock->supply->uuid)->allocatedTo($requisition->uuid))->toBe([]);
});

// ---------------------------------------------------------------- #6

test('#6 RIS series continues from the highest existing number, not the count', function () {
    $owner = gasuRequester();

    foreach (['RIS-2026-10-001', 'RIS-2026-10-002', 'RIS-2026-10-003'] as $ris) {
        Requisition::create(['uuid' => (string) Str::uuid(), 'user_id' => $owner->id, 'ris' => $ris, 'completed' => false]);
    }
    Requisition::where('ris', 'RIS-2026-10-001')->delete();

    $component = Livewire::actingAs($owner)->test(RequestDetail::class)->call('generateRIS');

    expect($component->get('requestForm.ris'))->toBe('RIS-2026-10-004');
});

test('#6 requisition update always validates unique RIS and existing users', function () {
    $approver = gasuApprover();
    $owner = gasuRequester();
    Requisition::create(['uuid' => (string) Str::uuid(), 'user_id' => $owner->id, 'ris' => 'RIS-2026-10-001', 'completed' => false]);
    $requisition = gasuRequisition($owner, [gasuLot(gasuSupply())->id => 1]);

    Livewire::actingAs($approver)
        ->test(RequestDetail::class)
        ->call('view', $requisition->id)
        ->set('requestForm.ris', 'RIS-2026-10-002')
        ->set('requestForm.approved_by', 999999)
        ->call('update')
        ->assertHasErrors(['requestForm.approved_by' => 'exists']);

    Livewire::actingAs($approver)
        ->test(RequestDetail::class)
        ->call('view', $requisition->id)
        ->set('requestForm.ris', 'RIS-2026-10-001')
        ->call('update')
        ->assertHasErrors(['requestForm.ris' => 'unique']);

    // Re-saving with its own RIS is fine (unique ignores the current row).
    $requisition->update(['ris' => 'RIS-2026-10-005']);
    Livewire::actingAs($approver)
        ->test(RequestDetail::class)
        ->call('view', $requisition->id)
        ->call('update')
        ->assertHasNoErrors();

    expect($requisition->fresh()->ris)->toBe('RIS-2026-10-005')
        ->and($requisition->fresh()->approved_by)->toBeNull();
});

// ---------------------------------------------------------------- #7

test('#7 viewing a requisition only generates an RIS when it has none', function () {
    $owner = gasuRequester();
    $withRis = Requisition::create(['uuid' => (string) Str::uuid(), 'user_id' => $owner->id, 'ris' => 'RIS-2026-09-007', 'completed' => false]);
    $withoutRis = Requisition::create(['uuid' => (string) Str::uuid(), 'user_id' => $owner->id, 'completed' => false]);

    Livewire::actingAs($owner)
        ->test(RequestDetail::class)
        ->call('view', $withRis->id)
        ->assertNotDispatched('generate-ris')
        ->assertSet('requestForm.ris', 'RIS-2026-09-007');

    Livewire::actingAs($owner)
        ->test(RequestDetail::class)
        ->call('view', $withoutRis->id)
        ->assertDispatched('generate-ris');
});

// ---------------------------------------------------------------- #8

test('#8 a requisition owner cannot set approval or issuance details', function () {
    $owner = gasuRequester();
    $requisition = gasuRequisition($owner, [gasuLot(gasuSupply())->id => 1]);

    Livewire::actingAs($owner)
        ->test(RequestDetail::class)
        ->call('view', $requisition->id)
        ->set('requestForm.approved_by', $owner->id)
        ->set('requestForm.issued_by', $owner->id)
        ->set('requestForm.approved_date', '2026-10-02')
        ->set('requestForm.issued_date', '2026-10-02')
        ->call('update')
        ->assertHasErrors(['requestForm.approved_by', 'requestForm.issued_by', 'requestForm.approved_date', 'requestForm.issued_date']);

    expect($requisition->fresh())
        ->approved_by->toBeNull()
        ->issued_by->toBeNull()
        ->approved_date->toBeNull()
        ->issued_date->toBeNull();

    // Owner can still save the fields that are theirs.
    Livewire::actingAs($owner)
        ->test(RequestDetail::class)
        ->call('view', $requisition->id)
        ->set('requestForm.purpose', 'Office use')
        ->call('update')
        ->assertHasNoErrors();

    expect($requisition->fresh()->purpose)->toBe('Office use');
});

test('#8 an approver can set approval and issuance details', function () {
    $owner = gasuRequester();
    $approver = gasuApprover();
    $requisition = gasuRequisition($owner, [gasuLot(gasuSupply())->id => 1]);

    Livewire::actingAs($approver)
        ->test(RequestDetail::class)
        ->call('view', $requisition->id)
        ->set('requestForm.approved_by', $approver->id)
        ->set('requestForm.issued_by', $approver->id)
        ->call('update')
        ->assertHasNoErrors();

    expect($requisition->fresh()->approved_by)->toBe($approver->id);
});

// ---------------------------------------------------------------- #9

test('#9 a stock lot that was requested cannot be deleted', function () {
    $stock = gasuLot(gasuSupply(), 10);
    gasuRequisition(gasuRequester(), [$stock->id => 2]);
    $events = gasuStoredEventCount();

    Livewire::actingAs(gasuStockManager())
        ->test(StockTable::class)
        ->call('delete', $stock->id);

    expect(Stock::find($stock->id))->not->toBeNull()
        ->and(gasuStoredEventCount())->toBe($events);
});

test('#9 deleting an unused lot goes through the aggregate so it is never allocated afterwards', function () {
    $supply = gasuSupply();
    $unused = gasuLot($supply, 5, 'stock-001');
    $other = gasuLot($supply, 5, 'stock-002');

    Livewire::actingAs(gasuStockManager())
        ->test(StockTable::class)
        ->call('delete', $unused->id);

    expect(Stock::find($unused->id))->toBeNull()
        ->and(SupplyAggregate::retrieve($supply->uuid)->hasLot('stock-001'))->toBeFalse();

    // Only 5 left in the supply: requesting 8 is a validation error, not an
    // over-issue followed by ModelNotFoundException.
    $owner = gasuRequester();
    Livewire::actingAs($owner)
        ->test(RequisitionTable::class)
        ->set("itemForm.requestedItems.{$other->id}", 8)
        ->call('create')
        ->assertHasErrors('itemForm.requestedItems');

    expect($other->fresh()->quantity)->toBe(5)
        ->and(Requisition::count())->toBe(0);
});

// ---------------------------------------------------------------- #10

test('#10 a duplicate stock number for the same supply is rejected before anything is recorded', function () {
    $supply = gasuSupply();
    $existing = gasuLot($supply, 10, 'stock-001');
    $events = gasuStoredEventCount();

    Livewire::actingAs(gasuStockManager())
        ->test(StockTable::class)
        ->set('stockForm.supply_id', $supply->id)
        ->set('stockForm.barcode', 'BC-2')
        ->set('stockForm.stock_number', 'stock-001')
        ->set('stockForm.quantity', 3)
        ->set('stockForm.price', 10)
        ->set('stockForm.stock_location', 'Main')
        ->call('create')
        ->assertHasErrors(['stockForm.stock_number' => 'unique']);

    expect(gasuStoredEventCount())->toBe($events)
        ->and($existing->fresh()->quantity)->toBe(10);

    expect(fn () => SupplyAggregate::retrieve($supply->uuid)->addLot('stock-001', null, null, 1.0))
        ->toThrow(DuplicateStockLotException::class);
});

test('#10 the same stock number is still allowed for a different supply', function () {
    gasuLot(gasuSupply('Bond Paper'), 10, 'stock-001');
    $other = gasuSupply('Ballpen');

    Livewire::actingAs(gasuStockManager())
        ->test(StockTable::class)
        ->set('stockForm.supply_id', $other->id)
        ->set('stockForm.barcode', 'BC-2')
        ->set('stockForm.stock_number', 'stock-001')
        ->set('stockForm.quantity', 3)
        ->set('stockForm.price', 10)
        ->set('stockForm.stock_location', 'Main')
        ->call('create')
        ->assertHasNoErrors();

    expect(Stock::where('supply_id', $other->id)->value('quantity'))->toBe(3);
});

// ---------------------------------------------------------------- #11

test('#11 a purchase order is received into the selected stock, not another supply sharing its stock number', function () {
    $first = gasuLot(gasuSupply('Bond Paper'), 10, 'stock-001');
    $second = gasuLot(gasuSupply('Ballpen'), 10, 'stock-001');

    Livewire::actingAs(gasuStockManager())
        ->test(StockTable::class)
        ->call('selectStock', $second->id)
        ->set('stockForm.quantity', 5)
        ->call('savePurchaseStock')
        ->assertHasNoErrors();

    expect($first->fresh()->quantity)->toBe(10)
        ->and($second->fresh()->quantity)->toBe(15);
});

// ---------------------------------------------------------------- #12

test('#12 purchase order quantity must be a positive integer', function ($bad) {
    $stock = gasuLot(gasuSupply(), 10);

    Livewire::actingAs(gasuStockManager())
        ->test(StockTable::class)
        ->call('selectStock', $stock->id)
        ->set('stockForm.quantity', $bad)
        ->call('savePurchaseStock')
        ->assertHasErrors('stockForm.quantity');

    expect($stock->fresh()->quantity)->toBe(10);
})->with([-20, 0, '2.5']);

// ---------------------------------------------------------------- #13

test('#13 requesting more than is on hand is a validation error and leaves nothing behind', function () {
    $stock = gasuLot(gasuSupply(), 5);
    $events = gasuStoredEventCount();

    $component = Livewire::actingAs(gasuRequester())
        ->test(RequisitionTable::class)
        ->set("itemForm.requestedItems.{$stock->id}", 6)
        ->call('create')
        ->assertHasErrors('itemForm.requestedItems');

    expect($component->errors()->first('itemForm.requestedItems'))->toContain('Only 5 Bond Paper available');

    expect(Requisition::count())->toBe(0)
        ->and($stock->fresh()->quantity)->toBe(5)
        ->and(gasuStoredEventCount())->toBe($events);
});

test('#13 a negative quantity is a validation error', function () {
    $stock = gasuLot(gasuSupply(), 5);

    $component = Livewire::actingAs(gasuRequester())
        ->test(RequisitionTable::class)
        ->set("itemForm.requestedItems.{$stock->id}", -3)
        ->call('create')
        ->assertHasErrors('itemForm.requestedItems');

    expect($component->errors()->first('itemForm.requestedItems'))->toContain('at least 1');

    expect(Requisition::count())->toBe(0)
        ->and($stock->fresh()->quantity)->toBe(5);
});

test('#13 if any item is short, no item is allocated', function () {
    $plenty = gasuLot(gasuSupply('Bond Paper'), 10);
    $short = gasuLot(gasuSupply('Ballpen'), 2);

    $component = Livewire::actingAs(gasuRequester())
        ->test(RequisitionTable::class)
        ->set("itemForm.requestedItems.{$plenty->id}", 3)
        ->set("itemForm.requestedItems.{$short->id}", 5)
        ->call('create')
        ->assertHasErrors('itemForm.requestedItems');

    expect($component->errors()->first('itemForm.requestedItems'))->toContain('Only 2 Ballpen available');

    expect(Requisition::count())->toBe(0)
        ->and(RequisitionItem::count())->toBe(0)
        ->and($plenty->fresh()->quantity)->toBe(10)
        ->and($short->fresh()->quantity)->toBe(2);
});

test('#13 a stock shortage discovered at allocation time leaves no partial requisition', function () {
    $plenty = gasuLot(gasuSupply('Bond Paper'), 10);
    $short = gasuLot(gasuSupply('Ballpen'), 5);

    $me = gasuRequester();

    $component = Livewire::actingAs($me)
        ->test(RequisitionTable::class)
        ->set("itemForm.requestedItems.{$plenty->id}", 3)
        ->set("itemForm.requestedItems.{$short->id}", 5);

    // Someone else takes the stock between validation and allocation: make
    // the read model look sufficient while the aggregate is not.
    gasuRequisition(gasuRequester(), [$short->id => 4]);
    Stock::whereKey($short->id)->update(['quantity' => 5]);
    $this->actingAs($me);

    $component->call('create')->assertHasErrors('itemForm.requestedItems');

    expect($component->errors()->first('itemForm.requestedItems'))->toContain('Not enough stock');

    expect(Requisition::where('user_id', $me->id)->exists())->toBeFalse();

    expect(Requisition::count())->toBe(1)
        ->and($plenty->fresh()->quantity)->toBe(10);
});

test('#13 allocating several lines is all-or-nothing at the action level too', function () {
    $owner = gasuRequester();
    $this->actingAs($owner);
    $plenty = gasuLot(gasuSupply('Bond Paper'), 10);
    $short = gasuLot(gasuSupply('Ballpen'), 2);
    $requisition = app(CreateRequestAction::class)->handle(['user_id' => $owner->id]);

    expect(fn () => app(AllocateStockToRequisitionAction::class)->handle($requisition, [$plenty->id => 3, $short->id => 5]))
        ->toThrow(\App\Domain\Gasu\Exceptions\InsufficientStockException::class);

    expect($plenty->fresh()->quantity)->toBe(10)
        ->and($requisition->items()->count())->toBe(0);
});

test('#13 a valid multi-item request allocates every item', function () {
    $a = gasuLot(gasuSupply('Bond Paper'), 10);
    $b = gasuLot(gasuSupply('Ballpen'), 5);

    Livewire::actingAs(gasuRequester())
        ->test(RequisitionTable::class)
        ->set("itemForm.requestedItems.{$a->id}", 3)
        ->set("itemForm.requestedItems.{$b->id}", 5)
        ->set("itemForm.requestedItems.999999", '')
        ->call('create')
        ->assertHasNoErrors();

    expect(Requisition::count())->toBe(1)
        ->and($a->fresh()->quantity)->toBe(7)
        ->and($b->fresh()->quantity)->toBe(0);
});

// ---------------------------------------------------------------- #14

test('#14 typed quantities survive searching in the request modal', function () {
    $stock = gasuLot(gasuSupply(), 10);

    $component = Livewire::actingAs(gasuRequester())
        ->test(RequisitionTable::class)
        ->set("itemForm.requestedItems.{$stock->id}", 3)
        ->set('search', 'Bond');

    expect($component->get('itemForm.requestedItems'))->toBe([$stock->id => 3]);
});

// ---------------------------------------------------------------- #15

test('#15 clicking the List tab calls an existing refreshStates action', function () {
    Livewire::actingAs(gasuRequester())
        ->test(RequisitionTable::class)
        ->set('search', 'Bond')
        ->call('refreshStates')
        ->assertSet('search', '');
});

// ---------------------------------------------------------------- #16

test('#16 RSMI generation does not crash when the issued requisition was deleted', function () {
    $stock = gasuLot(gasuSupply(), 10);
    $transaction = Transaction::create([
        'stock_id' => $stock->id,
        'requisition_id' => 987654,
        'quantity' => 2,
        'current_quantity' => 8,
        'type_of_transaction' => 'RIS',
    ]);

    $file = (new GenerateRsmiService())->handle(collect([$transaction->fresh()]), ['2026-10-01', '2026-10-31'], $transaction);
    $path = storage_path('app/public/' . $file);

    try {
        expect(IOFactory::load($path)->getActiveSheet()->getCell('B13')->getValue())->toBe('N/A');
    } finally {
        @unlink($path);
    }
});

// ---------------------------------------------------------------- #17

test('#17 RPCI generation does not crash when the requester has no employee record', function () {
    $requester = User::factory()->create();
    $requisition = Requisition::create(['uuid' => (string) Str::uuid(), 'user_id' => $requester->id, 'ris' => 'RIS-2026-10-001', 'completed' => true]);

    $details = [[
        'stock_id' => 1,
        'stock_name' => 'Bond Paper',
        'stock_number' => 'stock-001',
        'unit_measure' => 'ream',
        'unit_value' => 100,
        'po' => ['transactions' => [], 'total_quantity' => 0],
        'ris' => ['transactions' => [[
            'type_of_transaction' => 'RIS',
            'quantity' => 2,
            'current_quantity' => 8,
            'created_at' => '2026-10-02 09:00:00',
        ]], 'total_quantity' => 2],
        'net_quantity' => -2,
    ]];

    $file = (new GenerateRpciService())->handle($details, collect([$requisition->fresh()]));

    @unlink(storage_path('app/public/rpci/' . $file));

    expect($file)->toStartWith('rpci_');
});

// ---------------------------------------------------------------- #18

test('#18 RPCI issuance totals use the same date range as purchases', function () {
    $stock = gasuLot(gasuSupply(), 10); // PO of 10 on 2026-10-02

    $old = Transaction::create(['stock_id' => $stock->id, 'quantity' => 4, 'current_quantity' => 6, 'type_of_transaction' => 'RIS']);
    $old->forceFill(['created_at' => '2026-08-15 10:00:00', 'updated_at' => '2026-08-15 10:00:00'])->save();
    Transaction::create(['stock_id' => $stock->id, 'quantity' => 3, 'current_quantity' => 7, 'type_of_transaction' => 'RIS']);

    $captured = new ArrayObject();
    app()->instance(GenerateRpciService::class, new class($captured) extends GenerateRpciService
    {
        public function __construct(private ArrayObject $captured)
        {
        }

        public function handle(array $transactions, $requisition)
        {
            $this->captured['transactions'] = $transactions;

            return 'fake.xlsx';
        }
    });

    Livewire::actingAs(gasuApprover())
        ->test(RequestRpci::class)
        ->set('transactionDate', ['2026-10-01', '2026-10-31'])
        ->call('createRpci');

    $row = collect($captured['transactions'])->firstWhere('stock_id', $stock->id);

    expect($row['po']['total_quantity'])->toBe(10)
        ->and($row['ris']['total_quantity'])->toBe(3)
        ->and($row['net_quantity'])->toBe(7);
});

// ---------------------------------------------------------------- #19

test('#19 RSMI serials stay monotonic when a report is regenerated', function () {
    $stock = gasuLot(gasuSupply(), 10);
    $make = fn () => Transaction::create(['stock_id' => $stock->id, 'quantity' => 1, 'current_quantity' => 9, 'type_of_transaction' => 'RIS']);
    $service = new GenerateRsmiService();
    $files = [];

    $first = $make();
    $files[] = $service->handle(collect([$first]), ['2026-10-01', '2026-10-31'], $first);
    $files[] = $service->handle(collect([$first->fresh()]), ['2026-10-01', '2026-10-31'], $first->fresh()); // regenerate
    $second = $make();
    $files[] = $service->handle(collect([$second]), ['2026-10-01', '2026-10-31'], $second);

    $serials = collect($files)->map(fn ($file) => IOFactory::load(storage_path('app/public/' . $file))->getActiveSheet()->getCell('I7')->getValue());

    collect($files)->each(fn ($file) => @unlink(storage_path('app/public/' . $file)));

    expect($serials->all())->toBe(['Supply-2026-10-1', 'Supply-2026-10-2', 'Supply-2026-10-3']);
});

test('#19 the RSMI list exposes the generated file so the download button can show', function () {
    $stock = gasuLot(gasuSupply(), 10);
    Transaction::where('stock_id', $stock->id)->update(['rsmi_file' => 'rsmi/rsmi_2026-10-02_090000_Supply-2026-10-1.xls']);

    $component = Livewire::actingAs(gasuApprover())
        ->test(RequestRsmi::class)
        ->set('transactionDate', ['2026-10-01', '2026-10-31']);

    $row = $component->instance()->getTransactions->first();

    expect($row->stock_id)->toBe($stock->id)
        ->and($row->rsmi_file)->toBe('rsmi/rsmi_2026-10-02_090000_Supply-2026-10-1.xls');
});

// ---------------------------------------------------------------- #20

test('#20 the stock edit modal shows supply, stock number and quantity as read-only', function () {
    $stock = gasuLot(gasuSupply(), 10);

    Livewire::actingAs(gasuStockManager())
        ->test(StockTable::class)
        ->call('edit', $stock->id)
        ->assertSee('Use Purchase Order to add quantity')
        ->set('stockForm.quantity', 99)
        ->set('stockForm.barcode', 'NEW-BC')
        ->call('update')
        ->assertHasNoErrors();

    expect($stock->fresh())
        ->barcode->toBe('NEW-BC')
        ->quantity->toBe(10);
});

// ---------------------------------------------------------------- #21

test('#21 short real supply names like "Pen" are accepted', function () {
    Livewire::actingAs(gasuStockManager())
        ->test(SupplyTable::class)
        ->set('supplyForm.name', 'Pen')
        ->set('supplyForm.category', 'supplies')
        ->set('supplyForm.unit', 'piece')
        ->call('create')
        ->assertHasNoErrors();

    expect(Supply::where('name', 'Pen')->exists())->toBeTrue();
});

// ---------------------------------------------------------------- #23

test('#23 stock price allows large values up to decimal(15,2) and rejects anything beyond', function () {
    $supply = gasuSupply();

    $base = fn () => Livewire::actingAs(gasuStockManager())
        ->test(StockTable::class)
        ->set('stockForm.supply_id', $supply->id)
        ->set('stockForm.barcode', 'BC')
        ->set('stockForm.quantity', 1)
        ->set('stockForm.stock_location', 'Main');

    $base()->set('stockForm.stock_number', 'stock-big')
        ->set('stockForm.price', '100000000000000')
        ->call('create')
        ->assertHasErrors(['stockForm.price' => 'max']);

    $base()->set('stockForm.stock_number', 'stock-ok')
        ->set('stockForm.price', '1234567.89')
        ->call('create')
        ->assertHasNoErrors();

    expect((float) Stock::where('stock_number', 'stock-ok')->value('price'))->toBe(1234567.89);
});
