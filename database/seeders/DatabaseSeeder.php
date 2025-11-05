<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Procurement;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Section;
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
        $super = Role::create([
            'name' => 'Super Admin'
        ]);
        $user = Role::create(['name' => 'User']);

        $dave = User::factory()->create([
            'name' => 'Dave Madayag',
            'email' => 'dave@example.com',
        ]);

        $ray = User::factory()->create([
            'name' => 'Ray',
            'email' => 'ray@example.com'
        ]);

        $danny = User::factory()->create([
                'name' => 'Danny',
                'email' => 'danny@example.com'
            ]);
        $marvin = User::factory()->create([
                'name' => 'Marvin',
                'email' => 'marvin@example.com'
            ]);
        $jowee = User::factory()->create([
                'name' => 'Jowee',
                'email' => 'jowee@example.com'
            ]);

        $dave->assignRole($super);
        $ray->assignRole($super);

        $danny->assignRole($user);
        $marvin->assignRole($user);
        $jowee->assignRole($user);

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
                'name' => 'RMU',
                'description' => 'Records Management Unit',
            ],
            [
                'name' => 'HRMU',
                'description' => 'Human Resource Management Unit',
            ],
        ]);

        // Employee::create([
        //     'user_id' => $dave->id,
        //     'section_id' => $afms->id,
        //     'unit_id' => $afms->units()->where('name', 'GASU')->first()->id,
        // ]);

        // Employee::create([
        //         'user_id' => $ray->id,
        //         'section_id' => $afms->id,
        //         'unit_id' => $afms->units()->where('name', 'GASU')->first()->id,
        //     ]);

        // Employee::create([
        //     'user_id' => 3,
        //     'section_id' => $afms->id,
        //     'unit_id' => $afms->units()->where('name', 'MSS')->first()->id,
        //     ]);

        // Employee::create([
        //         'user_id' => 4,
        //         'section_id' => $afms->id,
        //         'unit_id' => $afms->units()->where('name', 'MSS')->first()->id,
        //     ]);
        // Employee::create( [
        //         'user_id' => 5,
        //         'section_id' => $afms->id,
        //         'unit_id' => $afms->units()->where('name', 'MSS')->first()->id,
        // ]);


        // supply
        Supply::create([
                'name' => 'Ballpen',
                'category' => 'Supplies',
                'unit' => 'Pc',

            ]);
        Supply::create([
                'name' => 'Notebook',
                'category' => 'Supplies',
                'unit' => 'Pc',

            ]);
        Supply::create([
                'name' => 'Printer Paper',
                'category' => 'Supplies',
                'unit' => 'Pc',
            ]);


        $date = now()->format('Y-m-d');
    }
}
