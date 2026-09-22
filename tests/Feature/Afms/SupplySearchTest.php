<?php

use App\Livewire\Pages\Afms\StockTable;
use App\Livewire\Pages\Afms\SupplyTable;
use App\Models\Stock;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('supply search matches a word anywhere in the name, not just the start', function () {
    $user = User::factory()->create();
    Supply::create(['name' => 'Dishwashing Liquid', 'category' => 'supplies', 'unit' => 'bottle']);
    Supply::create(['name' => 'Alcohol 50ml', 'category' => 'supplies', 'unit' => 'bottle']);

    $component = Livewire::actingAs($user)
        ->test(SupplyTable::class)
        ->set('search', 'Liquid');

    expect($component->instance()->rows->pluck('name')->all())->toBe(['Dishwashing Liquid']);
});

test('stock search matches a word anywhere in the supply name, not just the start', function () {
    $user = User::factory()->create();

    $liquid = Supply::create(['name' => 'Dishwashing Liquid', 'category' => 'supplies', 'unit' => 'bottle']);
    $alcohol = Supply::create(['name' => 'Alcohol 50ml', 'category' => 'supplies', 'unit' => 'bottle']);

    Stock::create([
        'supply_id' => $liquid->id,
        'quantity' => 10,
        'barcode' => 'DWL001',
        'stock_number' => 'stock-0001',
        'price' => 80,
        'initial_quantity' => 10,
        'stock_location' => 'Main Warehouse',
    ]);

    Stock::create([
        'supply_id' => $alcohol->id,
        'quantity' => 10,
        'barcode' => 'ALC001',
        'stock_number' => 'stock-0002',
        'price' => 150,
        'initial_quantity' => 10,
        'stock_location' => 'Main Warehouse',
    ]);

    $component = Livewire::actingAs($user)
        ->test(StockTable::class)
        ->set('search', 'Liquid');

    expect($component->instance()->rows->pluck('supply.name')->all())->toBe(['Dishwashing Liquid']);
});
