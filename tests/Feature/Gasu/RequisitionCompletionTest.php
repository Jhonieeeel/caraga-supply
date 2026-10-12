<?php

use App\Domain\Gasu\Aggregates\RequisitionAggregate;
use App\Domain\Gasu\Aggregates\SupplyAggregate;
use App\Domain\Gasu\Commands\AllocateStockToRequisitionHandler;
use App\Livewire\Pages\Afms\Components\RequestRIS;
use App\Models\Requisition;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('completing a requisition on its first-ever PDF upload succeeds and decrements stock exactly once', function () {
    Storage::fake('public');

    $supplyUuid = (string) Str::uuid();

    SupplyAggregate::retrieve($supplyUuid)
        ->createSupply('Bond Paper', 'Supplies', 'ream')
        ->addLot('LOT-A', 'BC-A', 'Shelf 1', 50.0, 10)
        ->persist();

    $stock = Stock::where('stock_number', 'LOT-A')->firstOrFail();

    $user = User::factory()->create();

    $requisitionUuid = (string) Str::uuid();

    RequisitionAggregate::retrieve($requisitionUuid)
        ->open($user->id, $user->id, null, 'Office supplies')
        ->persist();

    $requisition = Requisition::where('uuid', $requisitionUuid)->firstOrFail();

    app(AllocateStockToRequisitionHandler::class)->handle($supplyUuid, 'LOT-A', 4, $requisitionUuid);

    // Sanity: this really is a first-ever upload, matching the old bug's
    // trigger condition ($requisition->pdf starting out null).
    expect($requisition->fresh()->pdf)->toBeNull();

    Livewire::actingAs($user)
        ->test(RequestRIS::class)
        ->call('currentData', $requisition->id)
        ->set('temporaryFile', UploadedFile::fake()->create('signed.pdf', 10, 'application/pdf'))
        ->call('updateRIS')
        ->assertHasNoErrors();

    $requisition->refresh();

    expect((bool) $requisition->completed)->toBeTrue();
    expect($requisition->pdf)->not->toBeNull();
    expect($stock->fresh()->quantity)->toBe(6);
});
