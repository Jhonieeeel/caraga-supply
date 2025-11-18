<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Section;
use App\Models\Stock;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $super = Role::create([
            'name' => 'Super Admin'
        ]);

        $admin = Role::create([
            'name' => 'Admin'
        ]);

        $user = Role::create([
            'name' => 'User'
        ]);

        $dave = User::factory()->create([
            'name' => 'Dave Madayag',
            'email' => 'dave@example.com',
        ]);

        $mike = User::factory()->create([
            'name' => 'Mike Alsong',
            'email' => 'mike@example.com']);

        $rocky = User::factory()->create([
            'name' => 'Rocky Vagallon',
            'email' => 'rocky@example.com']);

        $dave->assignRole($super);
        $mike->assignRole($user);
        $rocky->assignRole($user);

        $afms = Section::create([
            'name' => 'AFMS',
            'description' => 'Administrative and Financial Management Section',
        ]);

        $afms->units()->createMany([
            [
                'name' => 'GASU',
                'description' => 'General Administrative Support Unit',
            ],
            [
                'name' => 'PMU',
                'description' => 'Procurement Management Unit',
            ],
            [
                'name' => 'RMU' ,
                'description' => 'Records Management Unit',
            ],
            [
                'name' => 'HRMU',
                'description' => 'Human Resource Management Unit',
            ],
        ]);

        Employee::create([
            'user_id' => $dave->id,
            'section_id' => $afms->id,
            'unit_id' => $afms->units()->firstWhere('name', 'GASU')->id,
        ]);

        Employee::create([
            'user_id' => $mike->id,
            'section_id' => $afms->id,
            'unit_id' => $afms->units()->firstWhere('name', 'GASU')->id,
        ]);

        Employee::create([
            'user_id' => $rocky->id,
            'section_id' => $afms->id,
            'unit_id' => $afms->units()->firstWhere('name', 'GASU')->id,
        ]);


        // suppliies
        $alcohol = Supply::create([
            'name' => 'Alcohol 50ml',
            'category' => 'supplies',
            'unit' => 'bottle',
        ]);

        Stock::create([
            'supply_id' => $alcohol->id,
            'quantity' => 100,
            'barcode' => 'ALC50ML001',
            'stock_number' => 'stk-0001',
            'price' => 150.00,
            'initial_quantity' => 100,
            'stock_location' => 'Main Warehouse',
        ]);

    }
}
