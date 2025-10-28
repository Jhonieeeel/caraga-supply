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
                'name' => 'MSS',
                'description' => 'Maintenance',
            ]
        ]);

        Employee::create([
            'user_id' => $dave->id,
            'section_id' => $afms->id,
            'unit_id' => $afms->units()->where('name', 'GASU')->first()->id,
        ]);

        Employee::create([
                'user_id' => $ray->id,
                'section_id' => $afms->id,
                'unit_id' => $afms->units()->where('name', 'GASU')->first()->id,
            ]);

        Employee::create([
            'user_id' => 3,
            'section_id' => $afms->id,
            'unit_id' => $afms->units()->where('name', 'MSS')->first()->id,
            ]);

        Employee::create([
                'user_id' => 4,
                'section_id' => $afms->id,
                'unit_id' => $afms->units()->where('name', 'MSS')->first()->id,
            ]);
        Employee::create( [
                'user_id' => 5,
                'section_id' => $afms->id,
                'unit_id' => $afms->units()->where('name', 'MSS')->first()->id,
        ]);


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

            // app
        $annual = Procurement::create([
                'code' => 'CARAGA-001',
                'project_title' => 'Procurement of 3-in-1 printer with ink for the conduct of Emergency Operations Center (EOC) Training Course Batch 1
',
                'pmo_end_user' => 'AFMS',
                'early_activity' => 'No',
                'mode_of_procurement' => 'NP-50-Direct Contracting',
                'advertisement_posting' => '2024-07-01',
                'submission_bids' => '1st Quarter',
                'notice_of_award' => '2nd Quarter',
                'contract_signing' => '3rd Quarter',
                'source_of_funds' => 'General Fund',
                'estimated_budget_total' => 50000,
                'estimated_budget_mooe' => 50000,
                'estimated_budget_co' => 0,
                'app_year' => 2024,
                'remarks' => 'Urgent procurement needed.',
            ]);

        $request = PurchaseRequest::create([
            'procurement_id' => $annual->id,
            'closing_date' => $date,
            'input_date' => $date,
            'app_spp_pdf_file' => 'app_test_.pdf',
            'app_spp_pdf_filename' => 'app_file',
            'philgeps_pdf_file' => 'philgeps_test.pdf',
            'philgeps_pdf_filename' => 'philgeps_test',
            'pr_number' => 'PR-001',
            'abc_based_app' => $annual->id,
            'abc' => 45000.00,
            'email_posting' => 'testlink@gmail.com',
            'date_posted' => $date,
            'app_year' => $annual->id,
        ]);

        PurchaseOrder::create([
            'purchase_request_id' => $request->id,
            'procurement_id' => $annual->id,
            'noa' => $date,
            'variance' => 123123,
            'po_number' => 'PO-001',
            'date_posted' => $request->id,
            'po_date' => $date,
            'delivery_date' => $date,
            'ntp' => $date,
            'resolution_number' => '2024-07-001',
            'supplier' => 'Test Supplier',
            'contact_price' => 15000,
            'email_link' => 'testpo@gmail.com',
            'abc_based_app' => $annual->id,
            'abc' => $request->id,
        ]);

    }
}
