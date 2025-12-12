<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Section;
use App\Models\Stock;
use App\Models\Supply;
use App\Models\Transaction;
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

        $guest = Role::create([
            'name' => 'Guest'
        ]);

        $dave = User::factory()->create([
            'name' => 'Dave Madayag',
            'email' => 'dave@example.com',
            'gender' => 'Male',
            'designation' => 'Designation 1',
            'office_position' => 'GASU HEAD'
        ]);

        $mike = User::factory()->create([
            'name' => 'Mike Alsong',
            'email' => 'mike@example.com',
             'gender' => 'Male',
            'designation' => 'Designation 2',
            'office_position' => 'Programmer'

        ]);

        $rocky = User::factory()->create([
            'name' => 'Rocky Vagallon',
            'email' => 'rocky@example.com',
            'gender' => 'Male',
            'designation' => 'Designation 3',
            'office_position' => 'Programmer',
        ]);

        $aizy = User::factory()->create([
            'name' => 'Aizy Lyn P Joloyohoy',
            'email' => 'aizy@ocdcaraga.com',
            'gender' => 'Female',
            'designation' => 'Designation 4',
            'office_position' => 'PMU HEAD'
        ]);

        $dave->assignRole($super);
        $aizy->assignRole($super);
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

        Employee::create([
            'user_id' => $aizy->id,
            'section_id' => $afms->id,
            'unit_id' => $afms->units()->firstWhere('name', 'PMU')->id,
        ]);


        // suppliies
        $alcohol = Supply::create([
            'name' => 'Alcohol 50ml',
            'category' => 'supplies',
            'unit' => 'bottle',
        ]);

        $soap = Supply::create([
            'name' => 'Soap',
            'category' => 'supplies',
            'unit' => 'pcs'
        ]);

        $dishwashing_liquid = Supply::create([
            'name' => 'Dishwashing Liquid',
            'category' => 'supplies',
            'unit' => 'bottle',
        ]);

        $stock_soap = Stock::create([
            'supply_id' => $soap->id,
            'quantity' => 200,
            'barcode' => 'SOAP001',
            'stock_number' => 'stk-0002',
            'price' => 50.00,
            'initial_quantity' => 200,
            'stock_location' => 'Main Warehouse',
        ]);

        $stock_dishwashing = Stock::create([
            'supply_id' => $dishwashing_liquid->id,
            'quantity' => 150,
            'barcode' => 'DWL001',
            'stock_number' => 'stk-0003',
            'price' => 80.00,
            'initial_quantity' => 150,
            'stock_location' => 'Main Warehouse',
        ]);

        $stock_alchol = Stock::create([
            'supply_id' => $alcohol->id,
            'quantity' => 100,
            'barcode' => 'ALC50ML001',
            'stock_number' => 'stk-0001',
            'price' => 150.00,
            'initial_quantity' => 100,
            'stock_location' => 'Main Warehouse',
        ]);

        // so every add og stock kay e add pod sa transaction considering nga ge addan og quantity
        Transaction::create([
            'stock_id' => $stock_alchol->id,
            'type_of_transaction' => 'PO',
            'quantity' => $stock_alchol->quantity,
            'initial_quantity' => $stock_alchol->initial_quantity,
        ]);

        Transaction::create([
            'stock_id' => $stock_dishwashing->id,
            'type_of_transaction' => 'PO',
            'quantity' => $stock_dishwashing->quantity,
            'initial_quantity' => $stock_dishwashing->initial_quantity,
        ]);

        Transaction::create([
            'stock_id' => $stock_soap->id,
            'type_of_transaction' => 'PO',
            'quantity' => $stock_soap->quantity,
            'initial_quantity' => $stock_soap->initial_quantity,
        ]);

    }
}
