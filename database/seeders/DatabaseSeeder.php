<?php

namespace Database\Seeders;

use App\Models\Supply;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $superRole = Role::create([
            'name' => 'Super Admin'
        ]);

        $userRole = Role::create([
            'name' => 'User'
        ]);

        $super = User::factory()->create([
            'name' => 'Dave Madayag',
            'email' => 'dave@example.com',
        ]);


        $ray = User::factory()->create([
            'name' => 'Ray Alingasa',
            'email' => 'ray@example.com',
        ]);

        $super->assignRole($superRole);
        $ray->assignRole($superRole);

        $user1 = User::factory()->create([
            'name' => 'Marvin',
            'email' => 'marvin@example.com',
        ]);

        $user2 = User::factory()->create([
            'name' => 'Danny',
            'email' => 'danny@example.com',
        ]);

        $user1->assignRole($userRole);
        $user2->assignRole($userRole);

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
