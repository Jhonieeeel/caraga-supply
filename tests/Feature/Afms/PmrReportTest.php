<?php

use App\Livewire\Pages\Afms\Pmr;
use App\Models\Procurement;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function pmrUser(): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::findByName('PMU'));

    return $user;
}

/**
 * One procurement per status: not started, ongoing (PR only), completed (PO delivered).
 */
function seedPmrData(): array
{
    $notStarted = Procurement::create([
        'code' => 'APP-2026-001', 'project_title' => 'Office Supplies', 'pmo_end_user' => 'GASU',
        'mode_of_procurement' => 'Small Value Procurement', 'app_year' => 2026,
        'estimated_budget_total' => 50000, 'estimated_budget_mooe' => 50000,
    ]);

    $ongoing = Procurement::create([
        'code' => 'APP-2026-002', 'project_title' => 'Training Venue', 'pmo_end_user' => 'Operations',
        'app_year' => 2026, 'estimated_budget_total' => 120000,
    ]);
    PurchaseRequest::create([
        'procurement_id' => $ongoing->id, 'pr_number' => 'PR-2026-09-001', 'date_posted' => '2026-09-01',
    ]);

    $completed = Procurement::create([
        'code' => 'APP-2026-003', 'project_title' => 'ICT Equipment', 'pmo_end_user' => 'ICT',
        'app_year' => 2026, 'estimated_budget_total' => 300000, 'estimated_budget_co' => 300000,
        'procurement_start' => '1st Quarter 2026', 'procurement_end' => '2nd Quarter 2026',
    ]);
    $pr = PurchaseRequest::create(['procurement_id' => $completed->id, 'pr_number' => 'PR-2026-03-004']);
    PurchaseOrder::create([
        'purchase_request_id' => $pr->id, 'procurement_id' => $completed->id,
        'noa' => '2026-04-10', 'delivery_date' => '2026-05-20',
        'contract_price' => 275000, 'supplier' => 'Caraga IT Solutions',
    ]);

    Procurement::create(['code' => 'APP-2025-009', 'project_title' => 'Old Year Item', 'app_year' => 2025]);

    return compact('notStarted', 'ongoing', 'completed');
}

test('the PMR route resolves to the PMR page, not the procurement detail page', function () {
    $route = app('router')->getRoutes()->match(\Illuminate\Http\Request::create('/pmu/pmr'));

    expect($route->getName())->toBe('pmr.index');
});

test('a PMU user can open the PMR and a GASU user cannot', function () {
    $gasu = User::factory()->create();
    $gasu->assignRole(Role::findByName('GASU'));

    $this->actingAs($gasu)->get(route('pmr.index'))->assertForbidden();

    seedPmrData();

    $this->actingAs(pmrUser())->get(route('pmr.index'))
        ->assertOk()
        ->assertSee('Procurement Monitoring Report')
        ->assertSee('APP-2026-003')
        ->assertSee('Caraga IT Solutions');
});

test('rows derive status, dates, contract cost, and savings from the PR and PO', function () {
    seedPmrData();

    $rows = Livewire::actingAs(pmrUser())->test(Pmr::class)->instance()->rows->keyBy('code');

    expect($rows->keys()->all())->toBe(['APP-2026-001', 'APP-2026-002', 'APP-2026-003']);

    expect($rows['APP-2026-001']['status'])->toBe('Not Yet Started');
    expect($rows['APP-2026-002']['status'])->toBe('Ongoing');
    expect($rows['APP-2026-002']['act_ads_post'])->toBe('Sep 01, 2026');
    expect($rows['APP-2026-002']['contract_cost'])->toBeNull();
    expect($rows['APP-2026-002']['savings'])->toBeNull();

    $done = $rows['APP-2026-003'];
    expect($done['status'])->toBe('Completed');
    expect($done['plan_start'])->toBe('1st Quarter 2026');
    expect($done['plan_end'])->toBe('2nd Quarter 2026');
    expect($done['act_noa'])->toBe('Apr 10, 2026');
    expect($done['act_delivery'])->toBe('May 20, 2026');
    expect($done['abc_total'])->toBe(300000.0);
    expect($done['contract_cost'])->toBe(275000.0);
    expect($done['savings'])->toBe(25000.0);
});

test('totals only count savings for awarded projects', function () {
    seedPmrData();

    $totals = Livewire::actingAs(pmrUser())->test(Pmr::class)->instance()->totals;

    expect((float) $totals['abc'])->toBe(470000.0);
    expect((float) $totals['contract_cost'])->toBe(275000.0);
    expect((float) $totals['savings'])->toBe(25000.0);
    expect($totals['counts'])->toBe(['Not Yet Started' => 1, 'Ongoing' => 1, 'Completed' => 1]);
});

test('year, status, and search filters narrow the rows', function () {
    seedPmrData();

    $component = Livewire::actingAs(pmrUser())->test(Pmr::class);

    expect($component->get('year'))->toBe(2026);

    $component->set('year', 2025);
    expect($component->instance()->rows->pluck('code')->all())->toBe(['APP-2025-009']);

    $component->set('year', null);
    expect($component->instance()->rows)->toHaveCount(4);

    $component->set('year', 2026)->set('status', 'Ongoing');
    expect($component->instance()->rows->pluck('code')->all())->toBe(['APP-2026-002']);

    $component->set('status', null)->set('search', 'ict');
    expect($component->instance()->rows->pluck('code')->all())->toBe(['APP-2026-003']);
});

test('CSV export contains the header row and one line per project', function () {
    seedPmrData();

    $component = Livewire::actingAs(pmrUser())->test(Pmr::class)->call('exportCsv');

    $component->assertFileDownloaded('PMR-2026.csv');

    $csv = base64_decode($component->effects['download']['content']);
    $lines = array_values(array_filter(preg_split('/\r?\n/', $csv)));

    expect($lines[0])->toContain('OCD Caraga - Procurement Monitoring Report (PMR) CY 2026');
    expect($lines[1])->toStartWith('Code,Program/Project,PMO/End-User');
    expect($lines)->toHaveCount(2 + 3);
    expect($csv)->toContain('APP-2026-003,"ICT Equipment",ICT');
    expect($csv)->toContain('275000');
});
