<?php

use App\Models\Requisition;
use App\Models\Stock;
use App\Models\Supply;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Afms\GenerateRsmiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;

uses(RefreshDatabase::class);

function makeTransactionForRsmi(): Transaction
{
    $supply = Supply::create(['name' => 'Bond Paper', 'category' => 'supplies', 'unit' => 'ream']);
    $stock = Stock::create([
        'supply_id' => $supply->id,
        'quantity' => 10,
        'barcode' => 'BP' . uniqid(),
        'stock_number' => 'stock-' . uniqid(),
        'price' => 100,
        'initial_quantity' => 10,
        'stock_location' => 'Main Warehouse',
    ]);

    $requisition = Requisition::create([
        'ris' => 'RIS-TEST-' . uniqid(),
        'user_id' => User::factory()->create()->id,
        'completed' => false,
    ]);

    return Transaction::create([
        'stock_id' => $stock->id,
        'requisition_id' => $requisition->id,
        'quantity' => 2,
        'current_quantity' => 8,
        'type_of_transaction' => 'RIS',
    ]);
}

test('rsmi serial number follows the Supply-year-month-series format', function () {
    $transaction = makeTransactionForRsmi();

    $rsmiDate = ['2026-09-01', '2026-09-30'];

    $file = (new GenerateRsmiService())->handle(collect([$transaction]), $rsmiDate, $transaction);

    $path = storage_path('app/public/' . $file);
    $spreadsheet = IOFactory::load($path);
    $serial = $spreadsheet->getActiveSheet()->getCell('I7')->getValue();

    expect($serial)->toBe('Supply-2026-09-1');

    @unlink($path);
});

test('rsmi serial series increments for additional reports generated in the same month', function () {
    $first = makeTransactionForRsmi();
    $rsmiDate = ['2026-09-01', '2026-09-30'];

    $firstFile = (new GenerateRsmiService())->handle(collect([$first]), $rsmiDate, $first);

    $second = makeTransactionForRsmi();
    $secondFile = (new GenerateRsmiService())->handle(collect([$second]), $rsmiDate, $second);

    $secondPath = storage_path('app/public/' . $secondFile);
    $spreadsheet = IOFactory::load($secondPath);
    $serial = $spreadsheet->getActiveSheet()->getCell('I7')->getValue();

    expect($serial)->toBe('Supply-2026-09-2');

    @unlink(storage_path('app/public/' . $firstFile));
    @unlink($secondPath);
});
