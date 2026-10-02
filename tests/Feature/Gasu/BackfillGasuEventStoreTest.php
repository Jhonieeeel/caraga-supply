<?php

use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Stock;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function createLegacySupplyWithStock(int $initialQuantity, int $requestedQty): array
{
    $supply = Supply::create(['name' => 'Bond Paper', 'category' => 'Supplies', 'unit' => 'ream']);

    $stock = Stock::create([
        'supply_id' => $supply->id,
        'barcode' => 'BC-A',
        'stock_number' => 'LOT-A',
        'quantity' => $initialQuantity - $requestedQty,
        'initial_quantity' => $initialQuantity,
        'price' => 50.0,
        'stock_location' => 'Shelf 1',
    ]);

    $user = User::factory()->create();

    $requisition = Requisition::create([
        'user_id' => $user->id,
        'requested_by' => $user->id,
        'completed' => true,
        'status' => 'completed',
        'pdf' => 'ris/SIGNED_RIS_legacy.pdf',
    ]);

    RequisitionItem::create([
        'requisition_id' => $requisition->id,
        'stock_id' => $stock->id,
        'requested_qty' => $requestedQty,
    ]);

    return compact('supply', 'stock', 'requisition');
}

test('backfill populates uuids and synthesizes a coherent event history reproducing current quantities', function () {
    ['supply' => $supply, 'stock' => $stock, 'requisition' => $requisition] = createLegacySupplyWithStock(10, 4);

    $this->artisan('gasu:backfill-events')->assertExitCode(0);

    expect($supply->fresh()->uuid)->not->toBeNull();
    expect($requisition->fresh()->uuid)->not->toBeNull();

    // Untouched by the backfill — it only ever reads current state and adds
    // uuid columns, never changes quantities/relations already on record.
    expect($stock->fresh()->quantity)->toBe(6);
    expect($stock->fresh()->initial_quantity)->toBe(10);

    $eventClasses = DB::table('stored_events')->pluck('event_class');

    expect($eventClasses)->toContain('App\\Domain\\Gasu\\Events\\SupplyCreated');
    expect($eventClasses)->toContain('App\\Domain\\Gasu\\Events\\StockLotAdded');
    expect($eventClasses)->toContain('App\\Domain\\Gasu\\Events\\StockReceived');
    expect($eventClasses)->toContain('App\\Domain\\Gasu\\Events\\RequisitionOpened');
    expect($eventClasses)->toContain('App\\Domain\\Gasu\\Events\\StockAllocatedToRequisition');
    expect($eventClasses)->toContain('App\\Domain\\Gasu\\Events\\RequisitionCompleted');

    // Running it again is a no-op (whereNull('uuid') scopes both passes) —
    // no duplicate events, no duplicate uuids assigned.
    $countAfterFirstRun = DB::table('stored_events')->count();
    $this->artisan('gasu:backfill-events')->assertExitCode(0);
    expect(DB::table('stored_events')->count())->toBe($countAfterFirstRun);
});

test('--dry-run reports without writing any events', function () {
    createLegacySupplyWithStock(10, 4);

    $this->artisan('gasu:backfill-events --dry-run')->assertExitCode(0);

    expect(DB::table('stored_events')->count())->toBe(0);
    expect(Supply::whereNotNull('uuid')->count())->toBe(0);
});

// NOTE: the command's preflight() also checks for (a) duplicate
// (supply_id, stock_number) pairs and (b) requisition_items pointing at a
// deleted stock_id — both are exercised in production BEFORE the
// 2026_09_28_100002 (unique stock_number index) and the existing
// requisition_items.stock_id foreign key are applied to a database with
// pre-existing bad data, which is exactly the scenario those checks guard.
// Once this app's own migrations have run (as they always have by the time
// RefreshDatabase finishes for this test file), the schema itself already
// forbids constructing either bad state via Eloquent or a raw insert, so
// there is no way to exercise those two branches through an integration
// test against this fully-migrated schema — the checks remain as defensive,
// intentionally-redundant code for the pre-migration production window the
// plan describes, not because they're expected to fire in a healthy app.
