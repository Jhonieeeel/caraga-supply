<?php

use App\Domain\Gasu\Aggregates\RequisitionAggregate;
use App\Domain\Gasu\Aggregates\SupplyAggregate;
use App\Domain\Gasu\Commands\AllocateStockToRequisitionHandler;
use App\Domain\Gasu\Exceptions\InsufficientStockException;
use App\Models\RequisitionItem;
use App\Models\Stock;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\EventSourcing\AggregateRoots\Exceptions\CouldNotPersistAggregate;

uses(RefreshDatabase::class);

afterEach(function () {
    Carbon::setTestNow();
});

function openRequisition(): string
{
    $uuid = (string) Str::uuid();

    RequisitionAggregate::retrieve($uuid)
        ->open(User::factory()->create()->id, null, null, null)
        ->persist();

    return $uuid;
}

test('anchor lot alone satisfies the request', function () {
    $supplyUuid = (string) Str::uuid();

    SupplyAggregate::retrieve($supplyUuid)
        ->createSupply('Bond Paper', 'Supplies', 'ream')
        ->addLot('LOT-A', 'BC-A', 'Shelf 1', 50.0, 10)
        ->persist();

    $requisitionUuid = openRequisition();

    app(AllocateStockToRequisitionHandler::class)->handle($supplyUuid, 'LOT-A', 6, $requisitionUuid);

    $stockA = Stock::where('stock_number', 'LOT-A')->firstOrFail();
    expect($stockA->quantity)->toBe(4);

    $item = RequisitionItem::where('stock_id', $stockA->id)->firstOrFail();
    expect($item->requested_qty)->toBe(6);
});

test('a request exceeding the anchor lot spills over to the supply\'s other lots oldest-first', function () {
    Carbon::setTestNow('2026-01-01 00:00:00');
    $supplyUuid = (string) Str::uuid();

    SupplyAggregate::retrieve($supplyUuid)
        ->createSupply('Bond Paper', 'Supplies', 'ream')
        ->addLot('LOT-A', 'BC-A', 'Shelf 1', 50.0, 10)
        ->persist();

    Carbon::setTestNow('2026-01-02 00:00:00');
    SupplyAggregate::retrieve($supplyUuid)
        ->addLot('LOT-B', 'BC-B', 'Shelf 2', 55.0, 20)
        ->persist();

    Carbon::setTestNow('2026-01-03 00:00:00');
    $requisitionUuid = openRequisition();

    app(AllocateStockToRequisitionHandler::class)->handle($supplyUuid, 'LOT-A', 15, $requisitionUuid);

    $stockA = Stock::where('stock_number', 'LOT-A')->firstOrFail();
    $stockB = Stock::where('stock_number', 'LOT-B')->firstOrFail();

    expect($stockA->quantity)->toBe(0);
    expect($stockB->quantity)->toBe(15);

    expect(RequisitionItem::where('stock_id', $stockA->id)->firstOrFail()->requested_qty)->toBe(10);
    expect(RequisitionItem::where('stock_id', $stockB->id)->firstOrFail()->requested_qty)->toBe(5);
});

test('a request exceeding total stock across all lots throws and persists nothing', function () {
    $supplyUuid = (string) Str::uuid();

    SupplyAggregate::retrieve($supplyUuid)
        ->createSupply('Bond Paper', 'Supplies', 'ream')
        ->addLot('LOT-A', 'BC-A', 'Shelf 1', 50.0, 10)
        ->addLot('LOT-B', 'BC-B', 'Shelf 2', 55.0, 20)
        ->persist();

    $requisitionUuid = openRequisition();

    $eventCountBefore = DB::table('stored_events')->count();

    expect(fn () => app(AllocateStockToRequisitionHandler::class)->handle($supplyUuid, 'LOT-A', 35, $requisitionUuid))
        ->toThrow(InsufficientStockException::class);

    expect(DB::table('stored_events')->count())->toBe($eventCountBefore);

    expect(Stock::where('stock_number', 'LOT-A')->firstOrFail()->quantity)->toBe(10);
    expect(Stock::where('stock_number', 'LOT-B')->firstOrFail()->quantity)->toBe(20);
    expect(RequisitionItem::count())->toBe(0);
});

test('a stale persist attempt against a supply already changed by another process throws a version conflict', function () {
    $supplyUuid = (string) Str::uuid();

    SupplyAggregate::retrieve($supplyUuid)
        ->createSupply('Bond Paper', 'Supplies', 'ream')
        ->addLot('LOT-A', 'BC-A', 'Shelf 1', 50.0, 10)
        ->persist();

    $staleView = SupplyAggregate::retrieve($supplyUuid);

    // A "concurrent" process allocates against the same supply first.
    app(AllocateStockToRequisitionHandler::class)->handle($supplyUuid, 'LOT-A', 4, openRequisition());

    // The stale view still thinks all 10 units are available; staging its
    // own allocation and persisting must be rejected rather than silently
    // overwriting the concurrent change.
    $staleView->allocate('LOT-A', 3, openRequisition());

    expect(fn () => $staleView->persist())->toThrow(CouldNotPersistAggregate::class);

    expect(Stock::where('stock_number', 'LOT-A')->firstOrFail()->quantity)->toBe(6);
});

test('the handler retries and recovers when a genuine race occurs mid-allocation, without corrupting quantity', function () {
    $supplyUuid = (string) Str::uuid();

    SupplyAggregate::retrieve($supplyUuid)
        ->createSupply('Bond Paper', 'Supplies', 'ream')
        ->addLot('LOT-A', 'BC-A', 'Shelf 1', 50.0, 10)
        ->persist();

    $racingRequisitionUuid = openRequisition();
    $requisitionUuid = openRequisition();

    $injected = false;

    // Fires once the handler-under-test has retrieved the supply's current
    // event stream but before it persists its own allocation — a second,
    // independent handler call commits first, exactly reproducing the
    // retrieve-then-someone-else-writes-then-persist race the retry exists
    // to recover from.
    DB::listen(function ($query) use (&$injected, $supplyUuid, $racingRequisitionUuid) {
        if ($injected) {
            return;
        }

        if (! str_contains($query->sql, 'stored_events') || ! in_array($supplyUuid, $query->bindings, true)) {
            return;
        }

        $injected = true;

        app(AllocateStockToRequisitionHandler::class)->handle($supplyUuid, 'LOT-A', 3, $racingRequisitionUuid);
    });

    app(AllocateStockToRequisitionHandler::class)->handle($supplyUuid, 'LOT-A', 4, $requisitionUuid);

    // 10 - 3 (the race that landed first) - 4 (this call, after retrying) = 3
    expect(Stock::where('stock_number', 'LOT-A')->firstOrFail()->quantity)->toBe(3);

    $stockA = Stock::where('stock_number', 'LOT-A')->firstOrFail();
    expect(RequisitionItem::where('stock_id', $stockA->id)->sum('requested_qty'))->toBe(7);
});
