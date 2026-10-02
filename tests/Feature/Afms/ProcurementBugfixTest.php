<?php

use App\Actions\Procurement\CreateOrder;
use App\Actions\Procurement\PurchaseRequest\LoadMealDraft;
use App\Actions\Procurement\PurchaseRequest\SaveMealDraft;
use App\Livewire\Pages\Afms\Components\ProcurementAnnual;
use App\Livewire\Pages\Afms\Components\ProcurementOrder;
use App\Livewire\Pages\Afms\Components\ProcurementRequest;
use App\Livewire\Pages\Afms\ShowData;
use App\Models\PrAccommodationItem;
use App\Models\PrMealItem;
use App\Models\Procurement;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function procurementBugfixUser(): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::findByName('PMU'));

    return $user;
}

function procurementBugfixMealBlocks(array $overrides = []): array
{
    return [[
        'lot_title' => 'Lot 1',
        'location' => 'Butuan City',
        'date' => 'March 2026',
        'items' => [[
            'pax_qty' => 30,
            'mealSnack' => 'Lunch',
            'arrangement' => 'Buffet',
            'delivery_date' => '2026-03-10',
            'menu' => 'Rice, chicken',
            'other_requirement' => 'Halal',
            'qty' => 30,
            'unit' => 'pax',
            'estimated_unit_cost' => 250,
        ] + ($overrides['item'] ?? [])],
        'accommodations' => [[
            'accommodation_title' => 'Hotel',
            'location' => 'Butuan City',
            'date' => 'March 2026',
            'items' => [[
                'no_of_pax' => 10,
                'room_requirement' => 'Twin sharing',
                'no_of_rooms' => 5,
                'check_in' => '2026-03-10 14:00:00',
                'check_out' => '2026-03-12 12:00:00',
                'no_of_nights' => 2,
                'other_requirement' => 'With breakfast',
                'qty' => 10,
                'unit' => 'room-night',
                'estimated_unit_cost' => 1800,
            ] + ($overrides['accommodation_item'] ?? [])],
        ]],
    ]];
}

/**
 * Compile a migration against the MySQL grammar (pretend mode, no server needed)
 * so the real production column types can be asserted; sqlite maps every
 * decimal to "numeric" and never overflows.
 */
function procurementBugfixMysqlSql(string $migrationFile): string
{
    config(['database.connections.mysql_pretend' => [
        'driver' => 'mysql',
        'database' => 'pretend',
        'prefix' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]]);

    $default = config('database.default');
    config(['database.default' => 'mysql_pretend']);

    try {
        $connection = app('db')->connection('mysql_pretend');
        $migration = require database_path('migrations/'.$migrationFile);

        $queries = $connection->pretend(fn () => $migration->up());
    } finally {
        config(['database.default' => $default]);
    }

    return collect($queries)->pluck('query')->implode(";\n");
}

// Bug 1 -----------------------------------------------------------------------

test('a meal draft with an accommodation item saves and reloads its other requirements', function () {
    $request = PurchaseRequest::create(['procurement_id' => Procurement::create(['code' => 'MEAL-1'])->id]);

    (new SaveMealDraft())->handle($request, procurementBugfixMealBlocks());

    $item = PrAccommodationItem::sole();
    expect($item->other_requirements)->toBe('With breakfast');

    $loaded = (new LoadMealDraft())->handle($request->fresh());
    expect($loaded[0]['accommodations'][0]['items'][0]['other_requirement'])->toBe('With breakfast')
        ->and($loaded[0]['items'][0]['other_requirement'])->toBe('Halal');
});

test('a failing meal draft save rolls back instead of wiping the saved draft', function () {
    $request = PurchaseRequest::create(['procurement_id' => Procurement::create(['code' => 'MEAL-2'])->id]);
    (new SaveMealDraft())->handle($request, procurementBugfixMealBlocks());

    PrAccommodationItem::creating(fn () => throw new RuntimeException('boom'));

    try {
        expect(fn () => (new SaveMealDraft())->handle($request, procurementBugfixMealBlocks(['item' => ['menu' => 'Changed']])))
            ->toThrow(RuntimeException::class);
    } finally {
        PrAccommodationItem::flushEventListeners();
    }

    expect(PrMealItem::sole()->menu)->toBe('Rice, chicken')
        ->and(PrAccommodationItem::count())->toBe(1);
});

// Bug 2 -----------------------------------------------------------------------

test('re-saving a meal draft keeps the PO unit costs entered on the PO print page', function () {
    $request = PurchaseRequest::create(['procurement_id' => Procurement::create(['code' => 'MEAL-3'])->id]);
    (new SaveMealDraft())->handle($request, procurementBugfixMealBlocks());

    // what ProcurementOrderPrint::savePoEdit does
    PrMealItem::query()->update(['po_estimated_unit_cost' => 230]);
    PrAccommodationItem::query()->update(['po_estimated_unit_cost' => 1700]);

    $blocks = (new LoadMealDraft())->handle($request->fresh());
    (new SaveMealDraft())->handle($request, $blocks);

    expect((float) PrMealItem::sole()->po_estimated_unit_cost)->toBe(230.0)
        ->and((float) PrAccommodationItem::sole()->po_estimated_unit_cost)->toBe(1700.0);
});

// Bug 3 / 11 ------------------------------------------------------------------

test('purchase order money columns are decimal(15,2) and address columns are text in MySQL', function () {
    $sql = procurementBugfixMysqlSql('2026_10_02_100000_widen_purchase_order_money_and_supplier_address_columns.php');

    expect($sql)->toContain('`variance` decimal(15, 2) null')
        ->toContain('`contract_price` decimal(15, 2) null')
        ->toContain('`supplier_address` text null')
        ->toContain('`supplier_contacts` text null')
        ->toContain('`address` text null');

    // the migrated sqlite schema reflects the text columns too
    expect(Schema::getColumnType('suppliers', 'address'))->toBe('text')
        ->and(Schema::getColumnType('purchase_orders', 'supplier_address'))->toBe('text');
});

test('money fields reject amounts wider than decimal(15,2)', function () {
    $tooBig = '10000000000000';
    $user = procurementBugfixUser();
    $procurement = Procurement::create(['code' => 'BIG-1']);
    $request = PurchaseRequest::create(['procurement_id' => $procurement->id]);

    Livewire::actingAs($user)
        ->test(ProcurementOrder::class)
        ->set('orderForm.purchase_request_id', $request->id)
        ->set('orderForm.contract_price', $tooBig)
        ->call('onSubmit')
        ->assertHasErrors(['orderForm.contract_price' => 'max']);

    Livewire::actingAs($user)
        ->test(ProcurementRequest::class)
        ->set('procurement_id', Procurement::create(['code' => 'BIG-2'])->id)
        ->set('requestForm.abc', $tooBig)
        ->call('onSubmit')
        ->assertHasErrors(['requestForm.abc' => 'max']);

    Livewire::actingAs($user)
        ->test(ProcurementAnnual::class)
        ->call('edit', $procurement->id)
        ->set('annualForm.estimated_budget_total', $tooBig)
        ->set('annualForm.estimated_budget_mooe', $tooBig)
        ->set('annualForm.estimated_budget_co', $tooBig)
        ->call('onUpdate')
        ->assertHasErrors([
            'annualForm.estimated_budget_total' => 'max',
            'annualForm.estimated_budget_mooe' => 'max',
            'annualForm.estimated_budget_co' => 'max',
        ]);

    expect(PurchaseOrder::count())->toBe(0);
});

test('a supplier with a 1000 character address can be created', function () {
    Livewire::actingAs(procurementBugfixUser())
        ->test(\App\Livewire\Pages\Afms\SupplierTable::class)
        ->set('supplierForm.business_name', 'Long Address Co')
        ->set('supplierForm.tin', '999-000-111')
        ->set('supplierForm.address', str_repeat('a', 1000))
        ->set('supplierForm.philgeps_membership', 'Red')
        ->call('create')
        ->assertHasNoErrors();

    expect(strlen(Supplier::sole()->address))->toBe(1000);
});

// Bug 4 -----------------------------------------------------------------------

test('editing a purchase order on the details page recomputes the variance', function () {
    $procurement = Procurement::create(['code' => 'VAR-1']);
    $request = PurchaseRequest::create(['procurement_id' => $procurement->id, 'abc' => 50000]);
    $order = PurchaseOrder::create([
        'purchase_request_id' => $request->id,
        'procurement_id' => $procurement->id,
        'contract_price' => 40000,
        'variance' => 10000,
    ]);

    Livewire::actingAs(procurementBugfixUser())
        ->test(ShowData::class, ['id' => $procurement->id])
        ->call('editOrder', $order->id)
        ->set('orderForm.contract_price', 45000)
        ->call('submitEditOrder')
        ->assertHasNoErrors();

    expect((float) $order->fresh()->variance)->toBe(5000.0)
        ->and((float) $order->fresh()->contract_price)->toBe(45000.0);
});

// Bug 5 / 6 -------------------------------------------------------------------

test('replacing a purchase order PDF deletes the old file from the public disk', function () {
    Storage::fake('public');
    Storage::disk('public')->put('po-records/old-ntp.pdf', 'old');

    $procurement = Procurement::create(['code' => 'PDF-1']);
    $request = PurchaseRequest::create(['procurement_id' => $procurement->id]);
    $order = PurchaseOrder::create([
        'purchase_request_id' => $request->id,
        'procurement_id' => $procurement->id,
        'ntp_pdf_file' => 'po-records/old-ntp.pdf',
    ]);

    Livewire::actingAs(procurementBugfixUser())
        ->test(ShowData::class, ['id' => $procurement->id])
        ->call('editOrder', $order->id)
        ->set('updateNtpFile', UploadedFile::fake()->create('new-ntp.pdf', 10, 'application/pdf'))
        ->call('submitEditOrder')
        ->assertHasNoErrors();

    Storage::disk('public')->assertMissing('po-records/old-ntp.pdf');
    expect($order->fresh()->ntp_pdf_file)->not->toBe('po-records/old-ntp.pdf');
    Storage::disk('public')->assertExists($order->fresh()->ntp_pdf_file);
});

test('replacing a purchase request PDF deletes the old file from the public disk', function () {
    Storage::fake('public');
    Storage::disk('public')->put('pr-records/old-app.pdf', 'old');

    $procurement = Procurement::create(['code' => 'PDF-2']);
    $request = PurchaseRequest::create([
        'procurement_id' => $procurement->id,
        'app_spp_pdf_file' => 'pr-records/old-app.pdf',
    ]);

    Livewire::actingAs(procurementBugfixUser())
        ->test(ShowData::class, ['id' => $procurement->id])
        ->call('editRequest', $request->id)
        ->set('updateAppFile', UploadedFile::fake()->create('new-app.pdf', 10, 'application/pdf'))
        ->call('submitEditRequest')
        ->assertHasNoErrors();

    Storage::disk('public')->assertMissing('pr-records/old-app.pdf');
    Storage::disk('public')->assertExists($request->fresh()->app_spp_pdf_file);
});

// Bug 7 -----------------------------------------------------------------------

test('submitting a purchase order without a purchase request shows a validation error', function () {
    Livewire::actingAs(procurementBugfixUser())
        ->test(ProcurementOrder::class)
        ->call('onSubmit')
        ->assertHasErrors(['orderForm.purchase_request_id' => 'required']);

    expect(PurchaseOrder::count())->toBe(0);
});

test('submitting a purchase order resolves the PR and variance from the selected request', function () {
    $procurement = Procurement::create(['code' => 'PO-1', 'quantity' => 1, 'unit_cost' => 100000]);
    $request = PurchaseRequest::create(['procurement_id' => $procurement->id, 'abc' => 100000]);

    Livewire::actingAs(procurementBugfixUser())
        ->test(ProcurementOrder::class)
        ->set('orderForm.purchase_request_id', $request->id)
        ->set('orderForm.contract_price', 80000)
        ->call('onSubmit')
        ->assertHasNoErrors();

    $order = PurchaseOrder::sole();
    expect($order->procurement_id)->toBe($procurement->id)
        ->and((float) $order->variance)->toBe(20000.0);
});

// Bug 8 -----------------------------------------------------------------------

test('a second purchase order for the same purchase request is rejected', function () {
    $procurement = Procurement::create(['code' => 'PO-2', 'quantity' => 1, 'unit_cost' => 100000]);
    $request = PurchaseRequest::create(['procurement_id' => $procurement->id, 'abc' => 100000]);

    $component = Livewire::actingAs(procurementBugfixUser())
        ->test(ProcurementOrder::class)
        ->set('orderForm.purchase_request_id', $request->id)
        ->set('orderForm.contract_price', 80000)
        ->call('onSubmit')
        ->assertHasNoErrors();

    $component
        ->set('orderForm.purchase_request_id', $request->id)
        ->set('orderForm.contract_price', 80000)
        ->call('onSubmit')
        ->assertHasErrors('orderForm.purchase_request_id')
        ->assertDispatched('alert');

    expect(PurchaseOrder::count())->toBe(1)
        ->and((float) $procurement->fresh()->remaining_budget)->toBe(20000.0);

    expect(fn () => (new CreateOrder())->handle([
        'purchase_request_id' => $request->id,
        'procurement_id' => $procurement->id,
        'contract_price' => 1000,
    ]))->toThrow(ValidationException::class);

    expect(PurchaseOrder::count())->toBe(1)
        ->and((float) $procurement->fresh()->remaining_budget)->toBe(20000.0);
});

// Bug 9 -----------------------------------------------------------------------

test('the annual procurement search respects the year filter', function () {
    Procurement::create(['code' => 'LAPTOP-2025', 'project_title' => 'Laptops', 'app_year' => 2025]);
    Procurement::create(['code' => 'LAPTOP-2026', 'project_title' => 'Laptops', 'app_year' => 2026]);
    Procurement::create(['code' => 'CHAIR-2026', 'project_title' => 'Chairs', 'app_year' => 2026]);

    $component = Livewire::actingAs(procurementBugfixUser())
        ->test(ProcurementAnnual::class)
        ->set('filterYear', 2026)
        ->set('search', 'Laptop');

    expect(collect($component->instance()->rows()->items())->pluck('code')->all())->toBe(['LAPTOP-2026']);
});

// Bug 10 ----------------------------------------------------------------------

test('adding a purchase request with invalid data shows no success alert and keeps the modal open', function () {
    $procurement = Procurement::create(['code' => 'PR-1']);

    Livewire::actingAs(procurementBugfixUser())
        ->test(ProcurementRequest::class)
        ->set('procurement_id', $procurement->id)
        ->set('requestForm.abc', 'not-a-number')
        ->call('onSubmit')
        ->assertHasErrors('requestForm.abc')
        ->assertNotDispatched('alert')
        ->assertNotDispatched('modal:add-request-close');

    expect(PurchaseRequest::count())->toBe(0);
});

test('adding a valid purchase request shows the success alert and closes the modal', function () {
    $procurement = Procurement::create(['code' => 'PR-2']);

    Livewire::actingAs(procurementBugfixUser())
        ->test(ProcurementRequest::class)
        ->set('procurement_id', $procurement->id)
        ->set('requestForm.abc', 5000)
        ->call('onSubmit')
        ->assertHasNoErrors()
        ->assertDispatched('alert')
        ->assertDispatched('modal:add-request-close');

    expect(PurchaseRequest::count())->toBe(1);
});

// Bug 12 ----------------------------------------------------------------------

test('the annual update alert is shown on save, not when opening the edit modal', function () {
    $procurement = Procurement::create(['code' => 'ANN-1', 'project_title' => 'Old title']);

    $component = Livewire::actingAs(procurementBugfixUser())
        ->test(ProcurementAnnual::class)
        ->call('edit', $procurement->id)
        ->assertDispatched('modal:edit-entry-open')
        ->assertNotDispatched('alert');

    $component
        ->set('annualForm.project_title', 'New title')
        ->call('onUpdate')
        ->assertHasNoErrors()
        ->assertDispatched('alert');

    expect($procurement->fresh()->project_title)->toBe('New title');
});

// Bug 13 ----------------------------------------------------------------------

test('a purchase order exposes both the supplier name column and the supplier record relation', function () {
    $supplier = Supplier::create(['business_name' => 'Caraga Office Supplies', 'tin' => '123-123-123']);
    $procurement = Procurement::create(['code' => 'SUP-1']);
    $request = PurchaseRequest::create(['procurement_id' => $procurement->id]);
    $order = PurchaseOrder::create([
        'purchase_request_id' => $request->id,
        'procurement_id' => $procurement->id,
        'supplier_id' => $supplier->id,
        'supplier' => 'Caraga Office Supplies',
    ]);

    $order = PurchaseOrder::with('supplierRecord')->find($order->id);

    expect($order->supplier)->toBe('Caraga Office Supplies')
        ->and($order->supplierRecord)->toBeInstanceOf(Supplier::class)
        ->and($order->supplierRecord->is($supplier))->toBeTrue()
        ->and(method_exists(PurchaseOrder::class, 'supplier'))->toBeFalse();
});

// Bug 14 ----------------------------------------------------------------------

test('the procurement details page returns 404 for an unknown id', function () {
    $this->actingAs(procurementBugfixUser())
        ->get(route('pmu.show', 999999))
        ->assertNotFound();

    expect(method_exists(ShowData::class, 'printOrder'))->toBeFalse();
});
