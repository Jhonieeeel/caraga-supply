<?php

use App\Actions\Procurement\ImportAppCsv;
use App\Livewire\Pages\Afms\Procurement as ProcurementPage;
use App\Models\Procurement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

/**
 * Builds a CSV laid out like the GPPB APP template (RA 12009), including the
 * section rows, totals and signatories that must be skipped.
 */
function gppbAppCsv(array $projects): string
{
    $header = ['PAP Code', 'Object Code, as applicable', "Project Title ", 'End-User or Implementing Unit',
        'General Description of the Project ', 'General Description of the Procurement Project',
        'Mode of Procurement ', 'To be covered by an Early Procurement Activity? (Yes/No)',
        'Criteria for Bid Evaluation', 'Start of Procurement Activity', 'End of Procurement Activity ',
        'Source of Fund', 'Estimated Budget / Approved Budget for the Contract (PhP)', '', ''];

    $rows = [$header, array_fill(0, 15, ''), array_merge(['Column 1', ''], array_fill(0, 13, 'Column')),
        array_merge(['Early and Regular Procurement Activities', '', 'General Requirements'], array_fill(0, 12, ''))];

    foreach ($projects as $p) {
        $rows[] = ['', '', $p['title'], $p['unit'], $p['title'], '', $p['mode'], $p['early'] ?? 'No', 'LCRB',
            $p['start'], $p['end'], $p['fund'], $p['budget'], 'N/A', $p['remarks'] ?? ''];
    }

    $rows[] = ['', '', '', '', '', '', 'Total Amount of Estimated Budget: ', '', '', '', '', '', '9,999,999.00', '', ''];
    $rows[] = ['', '', 'Prepared by:', '', 'Submitted by:', '', '', 'Recommended by: ', '', '', '', 'Approved by:', '', '', ''];
    $rows[] = ['', '', ' Date : July 02, 2026', '', ' Date : July 02, 2026', '', '', '', '', '', '', ' Date : ____', '', '', ''];

    $path = tempnam(sys_get_temp_dir(), 'app') . '.csv';
    $handle = fopen($path, 'w');
    foreach ($rows as $row) {
        fputcsv($handle, $row, escape: '');
    }
    fclose($handle);

    return $path;
}

function sampleProjects(): array
{
    return [
        [
            'title' => 'Provision of Venue, Meals and Snacks for Various Gawad KALASAG Activities (3 Lots) ' . str_repeat('Lot details. ', 60),
            'unit' => 'PDPS', 'mode' => "Section 35.9 Lease of Real Property and Venue \n(RA 12009)",
            'start' => '2nd Quarter 2026', 'end' => '2nd Quarter 2026', 'fund' => 'GAA 2026 (CDE)', 'budget' => '399,000.00',
        ],
        [
            'title' => 'Procurement of Office Supplies', 'unit' => 'AFMS', 'mode' => 'Section 34 Small Value Procurement (RA 12009)',
            'early' => 'Yes', 'start' => '4th Quarter 2025', 'end' => '1st Quarter 2026', 'fund' => 'GAA 2026 (DRRM)',
            'budget' => '21,700.00', 'remarks' => 'Tagged as Non-Essential per OCDCO Memo No. 4',
        ],
        [
            'title' => 'Construction of Regional Warehouse', 'unit' => 'OS', 'mode' => 'Section 27 Competitive Bidding (RA 12009)',
            'start' => '1st-4th Quarter of 2026', 'end' => '1st-4th Quarter of 2026', 'fund' => 'GAA 2026 (QRF)', 'budget' => '10,000,000.00',
        ],
    ];
}

test('imports only the project rows of a GPPB APP CSV', function () {
    $result = (new ImportAppCsv())->handle(gppbAppCsv(sampleProjects()));

    expect($result)->toBe(['year' => 2026, 'created' => 3, 'updated' => 0, 'total_budget' => 10420700.0]);
    expect(Procurement::count())->toBe(3);
    expect(Procurement::orderBy('code')->pluck('code')->all())->toBe(['APP-2026-001', 'APP-2026-002', 'APP-2026-003']);

    $venue = Procurement::where('pmo_end_user', 'PDPS')->first();
    expect(mb_strlen($venue->project_title))->toBeGreaterThan(255);
    expect($venue->mode_of_procurement)->toBe('Section 35.9 Lease of Real Property and Venue (RA 12009)');
    expect($venue->procurement_start)->toBe('2nd Quarter 2026');
    expect($venue->bid_evaluation_criteria)->toBe('LCRB');
    expect($venue->early_activity)->toBe('no');
    expect($venue->remarks)->toBeNull();
    expect($venue->app_year)->toBe(2026);

    $supplies = Procurement::where('pmo_end_user', 'AFMS')->first();
    expect($supplies->early_activity)->toBe('yes');
    expect($supplies->procurement_start)->toBe('4th Quarter 2025');
    expect($supplies->remarks)->toBe('Tagged as Non-Essential per OCDCO Memo No. 4');

    $warehouse = Procurement::where('pmo_end_user', 'OS')->first();
    expect((float) $warehouse->estimated_budget_total)->toBe(10000000.0);
    expect((float) $warehouse->remaining_budget)->toBe(10000000.0);
});

test('re-uploading a revised APP updates projects instead of duplicating them', function () {
    (new ImportAppCsv())->handle(gppbAppCsv(sampleProjects()));

    $supplies = Procurement::where('pmo_end_user', 'AFMS')->first();
    $supplies->decrement('remaining_budget', 5000); // a PO was issued against it

    $revised = sampleProjects();
    $revised[1]['budget'] = '25,000.00';
    $revised[] = [
        'title' => 'Provision of Transportation Services', 'unit' => 'OS', 'mode' => 'Section 34 Small Value Procurement (RA 12009)',
        'start' => '2nd Quarter 2026', 'end' => '2nd Quarter 2026', 'fund' => 'GAA 2026 (QRF)', 'budget' => '144,000.00',
    ];

    $result = (new ImportAppCsv())->handle(gppbAppCsv($revised));

    expect($result['created'])->toBe(1);
    expect($result['updated'])->toBe(3);
    expect(Procurement::count())->toBe(4);
    expect(Procurement::where('project_title', 'Provision of Transportation Services')->value('code'))->toBe('APP-2026-004');

    $supplies->refresh();
    expect((float) $supplies->estimated_budget_total)->toBe(25000.0);
    // ABC rose by 3,300, the 5,000 already used stays used
    expect((float) $supplies->remaining_budget)->toBe(25000.0 - 5000.0);
});

test('a file that is not in the GPPB APP layout is rejected without importing anything', function () {
    $path = tempnam(sys_get_temp_dir(), 'bad') . '.csv';
    file_put_contents($path, "code,title,end user\nA-1,Something,AFMS\n");

    expect(fn () => (new ImportAppCsv())->handle($path))->toThrow(InvalidArgumentException::class);
    expect(Procurement::count())->toBe(0);
});

test('the Upload CSV button imports the APP and reports the result', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findByName('PMU'));

    $file = UploadedFile::fake()->createWithContent('Updated-APP-2026.csv', file_get_contents(gppbAppCsv(sampleProjects())));

    Livewire::actingAs($user)->test(ProcurementPage::class)
        ->set('app_csv', $file)
        ->call('readCSV')
        ->assertHasNoErrors()
        ->assertRedirect(route('pmu.index'));

    expect(Procurement::count())->toBe(3);
    expect(session('message')['text'])->toBe('APP 2026: 3 new and 0 updated projects, total ABC ₱10,420,700.00.');
});

test('the Upload CSV button shows an error for a file in the wrong layout', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findByName('PMU'));

    $file = UploadedFile::fake()->createWithContent('old.csv', "code,title,end user\nA-1,Something,AFMS\n");

    Livewire::actingAs($user)->test(ProcurementPage::class)
        ->set('app_csv', $file)
        ->call('readCSV')
        ->assertRedirect(route('pmu.index'));

    expect(Procurement::count())->toBe(0);
    expect(session('message')['color'])->toBe('red');
});
