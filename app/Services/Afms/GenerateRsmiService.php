<?php

namespace App\Services\Afms;

use App\Models\Transaction;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class GenerateRsmiService
{
    public function handle($rsmi, $rsmiDate, Transaction $transaction)
    {
        $rsmiTemplate = public_path('templates/rsmi_template.xls');
        $spreadSheet = IOFactory::load($rsmiTemplate);
        $activeSheet = $spreadSheet->getActiveSheet();

        $start = Carbon::parse($rsmiDate[0])->startOfDay();

        $stockNumber = $rsmi->first()->stock->stock_number;
        $number = explode('-', $stockNumber);

        $report = 'For the month of ' . $start->format('F');

        $activeSheet->setCellValue('B5', $report);
        $activeSheet->setCellValue('I7', 'Supply-' . $start->format('Y') . '-' . $number[1]);
        $activeSheet->setCellValue('I8', Carbon::parse(now()->format('Y-m-d'))->format('F-j-Y'));

        $overallQuantity  = 0;
        $rowStart = 13;
        foreach ($rsmi as $item) {

            if ($item->type_of_transaction === "RIS") {
                $activeSheet->setCellValue("B{$rowStart}", $item->requisition->ris);
            } else {
                continue;
            }

            // $activeSheet->setCellValue("B{$rowStart}", $item->type_of_transaction === "RIS" ? $item->requisition->ris : continue);
            $activeSheet->setCellValue("D{$rowStart}", $item->stock->stock_number);
            $activeSheet->setCellValue("E{$rowStart}", $item->stock->supply->name);
            $activeSheet->setCellValue("F{$rowStart}", $item->stock->supply->unit);
            $activeSheet->setCellValue("G{$rowStart}", $item->quantity);
            $overallQuantity += $item->quantity;
            $rowStart++;
        }

        $firstRequisition = $rsmi->first();
        // $initialQty = $firstRequisition->stock->initial_quantity;

        $initialQty = $firstRequisition->stock->quantity + $overallQuantity;

        $activeSheet->setCellValue('D32', $initialQty);

        $directory = storage_path('app/public/rsmi');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $writer = new Xls($spreadSheet);
        $newFileName = 'rsmi_' . now()->format('Y-m-d_His') . '.xls';


        $relativePath = 'rsmi/' . $newFileName;
        $outputPath = storage_path('app/public/' . $relativePath);
        $transaction->rsmi_file = $relativePath;
        $transaction->save();

        $writer->save($outputPath);

        return $relativePath

        ;
    }
}
