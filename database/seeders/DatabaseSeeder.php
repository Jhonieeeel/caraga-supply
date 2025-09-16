<?php

namespace Database\Seeders;

use App\Models\Employee;
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

        $dave->assignRole($super);

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
            ]
        ]);

        Employee::create([
            'user_id' => $dave->id,
            'section_id' => $afms->id,
            'unit_id' => $afms->units()->where('name', 'GASU')->first()->id,
        ]);
    }
}
