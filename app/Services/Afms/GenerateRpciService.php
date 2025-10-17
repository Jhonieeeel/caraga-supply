<?php

namespace App\Services\Afms;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateRpciService
{
    public function handle($rpci, $stockId)
    {
        $srcFile = public_path('templates/rpci_template.xlsx');
        $spreadsheet = IOFactory::load($srcFile);

        //  pick sheet by name (if sheet names are stock IDs)
        $sheet = $spreadsheet->getSheetByName((string) $stockId);
        // or by index: $sheet = $spreadsheet->getSheet((int) $stockId);

        $templateRow = $sheet->getHighestRow(); // last row is the "template"
        $highestCol  = $sheet->getHighestColumn(); // e.g. "K"

        Log::info("Highestt Row:" . $sheet->getHighestRow());

        foreach ($rpci as $transaction) {
            $targetRow = $sheet->getHighestRow() + 1;

            // insert a new row
            $sheet->insertNewRowBefore($targetRow, 1);

            // copy entire template row (values + formulas + styles)
            $sourceRange = "E{$templateRow}:{$highestCol}{$templateRow}";
            $targetRange = "E{$targetRow}:{$highestCol}{$targetRow}";

            $sheet->duplicateStyle($sheet->getStyle($sourceRange), $targetRange);

            foreach (range("E", "K") as $col) {
                $srcCell = $sheet->getCell("{$col}{$templateRow}");
                $dstCell = $sheet->getCell("{$col}{$targetRow}");

                if ($srcCell->isFormula()) {
                    $formula = $srcCell->getValue();

                    $rowOffset = $targetRow - $templateRow;
                    $newFormula = preg_replace_callback(
                        '/([A-Z]+)(\d+)/',
                        function ($matches) use ($rowOffset) {
                            $col = $matches[1];
                            $row = (int)$matches[2] + $rowOffset;
                            return $col . $row;
                        },
                        $formula
                    );

                    $dstCell->setValueExplicit($newFormula, DataType::TYPE_FORMULA);
                } else {
                    $dstCell->setValue($srcCell->getValue());
                }
            }

            // fill requisition-specific values
            $sheet->setCellValue("E{$targetRow}", Carbon::parse($transaction->updated_at)->format('m/d/Y'));

            if ($transaction->type_of_transaction === "RIS") {
                $sheet->setCellValue("F{$targetRow}", $transaction->requisition->ris);
                $sheet->setCellValue("H{$targetRow}", $transaction->quantity);
            } else {
                $date = Carbon::parse($transaction->created_at)->format('Y-m-d'); // PO-202

                $sheet->setCellValue("F{$targetRow}", $transaction->type_of_transaction . "{$date}");
                $sheet->setCellValue("G{$targetRow}", $transaction->quantity);
            }

            // keep row height
            $sheet->getRowDimension($targetRow)
                ->setRowHeight($sheet->getRowDimension($templateRow)->getRowHeight());
        }

        $directory = storage_path('app/public/rpci');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $newFileName = 'rpci_' . now()->format('Y-m-d_His') . '.xlsx';
        $outputPath = $directory . DIRECTORY_SEPARATOR . $newFileName;

        $writer->save($outputPath);

        return;
    }
}
