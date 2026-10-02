<?php

use App\Actions\Procurement\CreateOrder;
use App\Actions\Procurement\UpdateOrder;
use App\Livewire\Pages\Afms\ShowData;
use App\Models\Procurement;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('total_abc and remaining_budget are auto-computed on create', function () {
    $procurement = Procurement::create([
        'code' => 'PROC-100',
        'quantity' => 10,
        'unit_cost' => 100,
    ]);

    expect($procurement->total_abc)->toEqual(1000);
    expect((float) $procurement->remaining_budget)->toBe(1000.0);
    expect($procurement->remaining_quantity)->toBe(10);
});

test('remaining_budget depletes and restores across CreateOrder, UpdateOrder, and delete', function () {
    $procurement = Procurement::create([
        'code' => 'PROC-101',
        'quantity' => 10,
        'unit_cost' => 100,
    ]);

    $purchaseRequest = PurchaseRequest::create(['procurement_id' => $procurement->id]);

    $order = (new CreateOrder())->handle([
        'purchase_request_id' => $purchaseRequest->id,
        'procurement_id' => $procurement->id,
        'contract_price' => 300,
    ]);

    expect((float) $procurement->fresh()->remaining_budget)->toBe(700.0);

    (new UpdateOrder())->handle($order, ['contract_price' => 500]);

    expect((float) $procurement->fresh()->remaining_budget)->toBe(500.0);

    $user = User::factory()->create();
    $user->assignRole(Role::findByName('PMU'));

    Livewire::actingAs($user)
        ->test(ShowData::class, ['id' => $procurement->id])
        ->call('deleteOrder', $order->id);

    expect((float) $procurement->fresh()->remaining_budget)->toBe(1000.0);
});

test('deleting a purchase request refunds the budget of the purchase orders deleted with it', function () {
    $procurement = Procurement::create([
        'code' => 'PROC-102',
        'quantity' => 10,
        'unit_cost' => 100,
    ]);

    $purchaseRequest = PurchaseRequest::create(['procurement_id' => $procurement->id]);

    (new CreateOrder())->handle([
        'purchase_request_id' => $purchaseRequest->id,
        'procurement_id' => $procurement->id,
        'contract_price' => 300,
    ]);

    expect((float) $procurement->fresh()->remaining_budget)->toBe(700.0);

    $user = User::factory()->create();
    $user->assignRole(Role::findByName('PMU'));

    Livewire::actingAs($user)
        ->test(ShowData::class, ['id' => $procurement->id])
        ->call('deleteRequest', $purchaseRequest->id);

    expect(PurchaseRequest::count())->toBe(0);
    expect(\App\Models\PurchaseOrder::count())->toBe(0);
    expect((float) $procurement->fresh()->remaining_budget)->toBe(1000.0);
});

test('deleting a purchase request with no priced order leaves the budget unchanged', function () {
    $procurement = Procurement::create([
        'code' => 'PROC-103',
        'quantity' => 10,
        'unit_cost' => 100,
    ]);

    $purchaseRequest = PurchaseRequest::create(['procurement_id' => $procurement->id]);

    // order created from "Submit to Order" before a contract price is entered
    \App\Models\PurchaseOrder::create([
        'purchase_request_id' => $purchaseRequest->id,
        'procurement_id' => $procurement->id,
    ]);

    $user = User::factory()->create();
    $user->assignRole(Role::findByName('PMU'));

    Livewire::actingAs($user)
        ->test(ShowData::class, ['id' => $procurement->id])
        ->call('deleteRequest', $purchaseRequest->id);

    expect(PurchaseRequest::count())->toBe(0);
    expect((float) $procurement->fresh()->remaining_budget)->toBe(1000.0);
});
