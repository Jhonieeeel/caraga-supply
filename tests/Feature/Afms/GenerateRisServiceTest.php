<?php

use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Section;
use App\Models\Stock;
use App\Models\Supply;
use App\Models\Unit;
use App\Models\Employee;
use App\Models\User;
use App\Services\Afms\GenerateRisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpWord\IOFactory;

uses(RefreshDatabase::class);

test('generated RIS document includes division, office, and designations', function () {
    $section = Section::create(['name' => 'AFMS']);
    $unit = Unit::create(['name' => 'GASU', 'section_id' => $section->id]);

    $requestedBy = User::factory()->create(['designation' => 'GASU Head']);
    Employee::create(['user_id' => $requestedBy->id, 'section_id' => $section->id, 'unit_id' => $unit->id]);

    $approvedBy = User::factory()->create(['designation' => 'Approving Officer']);
    $issuedBy = User::factory()->create(['designation' => 'Issuing Officer']);
    $receivedBy = User::factory()->create(['designation' => 'Receiving Officer']);

    $requisition = Requisition::create([
        'ris' => 'RIS-2026-09-TEST',
        'user_id' => $requestedBy->id,
        'requested_by' => $requestedBy->id,
        'approved_by' => $approvedBy->id,
        'issued_by' => $issuedBy->id,
        'received_by' => $receivedBy->id,
        'completed' => false,
    ]);

    $supply = Supply::create(['name' => 'Bond Paper', 'category' => 'supplies', 'unit' => 'ream']);
    $stock = Stock::create([
        'supply_id' => $supply->id,
        'quantity' => 10,
        'barcode' => 'BP001',
        'stock_number' => 'stock-0001',
        'price' => 100,
        'initial_quantity' => 10,
        'stock_location' => 'Main Warehouse',
    ]);
    RequisitionItem::create([
        'requisition_id' => $requisition->id,
        'stock_id' => $stock->id,
        'requested_qty' => 2,
    ]);

    $outputFile = (new GenerateRisService())->handle($requisition->fresh(['items.stock.supply']));

    $text = extractDocxText($outputFile);

    expect($text)
        ->toContain('DRRMD')
        ->toContain('AFMS-GASU')
        ->toContain('GASU Head')
        ->toContain('Approving Officer')
        ->toContain('Issuing Officer')
        ->toContain('Receiving Officer');

    @unlink($outputFile);
});

function extractDocxText(string $path): string
{
    $phpWord = IOFactory::load($path);
    $text = '';

    foreach ($phpWord->getSections() as $section) {
        foreach ($section->getElements() as $element) {
            $text .= extractElementText($element);
        }
    }

    return $text;
}

function extractElementText($element): string
{
    if (method_exists($element, 'getText')) {
        return $element->getText() . ' ';
    }

    if (method_exists($element, 'getElements')) {
        $text = '';
        foreach ($element->getElements() as $child) {
            $text .= extractElementText($child);
        }
        return $text;
    }

    if (method_exists($element, 'getRows')) {
        $text = '';
        foreach ($element->getRows() as $row) {
            foreach ($row->getCells() as $cell) {
                foreach ($cell->getElements() as $child) {
                    $text .= extractElementText($child);
                }
            }
        }
        return $text;
    }

    return '';
}
