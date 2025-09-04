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

        $supply = Supply::create([
            'name' => 'Alcohol, 1 Litre',
            'category' => 'Supplies',
            'unit' => 'Bottle'
        ]);

        $supply->stocks()->create([
            'barcode' => 'test-code',
            'price' => 200,
            'quantity' => 38,
            'stock_number' => 'Supply-2025',
            'initial_quantity' => 38
        ]);

        // Supply::factory(20)->create();
    }
}
