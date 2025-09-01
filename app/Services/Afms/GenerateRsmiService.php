<?php

namespace App\Services\Afms;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class GenerateRsmiService
{
    public function handle($rsmi, $rsmiDate)
    {
        $rsmiTemplate = public_path('rsmi_template.xls');
        $spreadSheet = IOFactory::load($rsmiTemplate);
        $activeSheet = $spreadSheet->getActiveSheet();

        $activeSheet->setCellValue('B5', 'For the month of ' . Carbon::parse($rsmiDate[0])->format('F Y'));
        $activeSheet->setCellValue('I7', 'Supply-' . Carbon::parse($rsmiDate[0])->format('Y') . '-1');
        $activeSheet->setCellValue('I8', Carbon::parse(now()->format('Y-m-d'))->format('F-j-Y'));

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
        $initialQty = optional($firstRequisition->items->first()->stock)->initial_quantity;
        $activeSheet->setCellValue('D32', $initialQty);

        $directory = storage_path('app/public');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $writer = new Xls($spreadSheet);
        $newFileName = 'rsmi_' . now()->format('Y-m') . '.xls';
        $outputPath = $directory . DIRECTORY_SEPARATOR . $newFileName;
        $writer->save($outputPath);
    }
}
