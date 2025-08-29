<?php

namespace Database\Seeders;

use App\Models\Supply;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $supply_1 = Supply::create([
            'name' => 'Alcohol 500ml',
            'category' => 'Supplies',
            'unit' => 'bottle',
        ]);

        $supply_1->stocks()->create([
            'barcode' => 'barcode-001',
            'stock_number' => 'stock-001',
            'quantity' => 50,
            'initial_quantity' => 50,
            'price' => 200
        ]);

        // 2

        $supply_2 = Supply::create([
            'name' => 'Silhig',
            'category' => 'Supplies',
            'unit' => 'pc',
        ]);

        $supply_2->stocks()->create([
            'barcode' => 'barcode-002',
            'stock_number' => 'stock-002',
            'quantity' => 20,
            'initial_quantity' => 20,
            'price' => 50
        ]);

        // 3

        $supply_3 = Supply::create([
            'name' => 'Dishwashing Liquid',
            'category' => 'Supplies',
            'unit' => 'bottle',
        ]);

        $supply_3->stocks()->create([
            'barcode' => 'barcode-003',
            'stock_number' => 'stock-003',
            'quantity' => 30,
            'initial_quantity' => 30,
            'price' => 150
        ]);

        // Supply::factory(20)->create();
    }
}
