<?php

namespace App\Services\Afms;

use PhpOffice\PhpSpreadsheet\IOFactory;

class GenerateRpciService
{

    public function handle()
    {
        $srcFile = public_path('rpci_template.xlsx');
        $spreadsheet = IOFactory::load($srcFile);

        // Pick sheet (0-based index or by name)
        $sheet = $spreadsheet->getSheet("1"); // first sheet
        // $sheet = $spreadsheet->getSheetByName('Sheet1');

        // Which row to copy? Let's take the last used row
        $sourceRow = $sheet->getHighestRow(); // e.g. 60
        $targetRow = $sourceRow + 1;

        // 1) Insert new row (so formatting/merged cells stay aligned)
        $sheet->insertNewRowBefore($targetRow, 1);

        // 2) Copy values + formulas + styles column by column
        $highestCol = $sheet->getHighestColumn(); // e.g. "K"
        $range = "E{$sourceRow}:{$highestCol}{$sourceRow}";

        // Copy styles for the whole row
        $sheet->duplicateStyle($sheet->getStyle($range), "E{$targetRow}:{$highestCol}{$targetRow}");

        // Copy cell contents
        foreach ($sheet->rangeToArray($range, null, true, true, true) as $rowData) {
            foreach ($rowData as $col => $value) {
                $sheet->setCellValue("{$col}{$targetRow}", $value);
            }
        }

        // 3) Copy row height
        $sheet->getRowDimension($targetRow)
            ->setRowHeight($sheet->getRowDimension($sourceRow)->getRowHeight());

        // Save new file
        $out = storage_path('app/public/rpci_template_test.xlsx');
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($out);

        return $sheet->getHighestRow();
    }
}
