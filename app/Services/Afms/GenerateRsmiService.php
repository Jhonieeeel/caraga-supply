<?php

namespace App\Services\Afms;

use App\Models\Requisition;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateRsmiService
{
    public function handle($rsmi, $rsmiDate)
    {
        $rsmiTemplate = storage_path('app/public/rsmi_template.xls');
        $spreadSheet = IOFactory::load($rsmiTemplate);
        $activeSheet = $spreadSheet->getActiveSheet();

        $activeSheet->setCellValue('B5', 'For the month of ' . Carbon::parse($rsmiDate[0])->format('F Y'));
        $activeSheet->setCellValue('I7', 'Supply-1-' . Carbon::parse($rsmiDate[0])->format('Y') . '-1');
        $activeSheet->setCellValue('I8', now());

        $rowStart = 13;
        foreach ($rsmi as $item) {
            $activeSheet->setCellValue("B{$rowStart}", $item->ris);
            $activeSheet->setCellValue("D{$rowStart}", $item->items->first()->stock->stock_number);
            $activeSheet->setCellValue("E{$rowStart}", $item->items->first()->stock->supply->name);
            $activeSheet->setCellValue("F{$rowStart}", $item->items->first()->stock->supply->unit);
            $activeSheet->setCellValue("G{$rowStart}", $item->items->first()->requested_qty);
            $rowStart++;
        }

        $firstRequisition = $rsmi->first();
        $initialQty = optional($firstRequisition->items->first()->stock)->quantity;
        $activeSheet->setCellValue('D27', $initialQty);

        $directory = storage_path('app/public');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $writer = new Xls($spreadSheet);
        $newFileName = 'rsmi_' . now()->format('Ymd_His') . '.xls';
        $outputPath = $directory . DIRECTORY_SEPARATOR . $newFileName;
        $writer->save($outputPath);
    }
}
