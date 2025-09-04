<?php

namespace App\Services\Afms;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class GenerateRpciService
{
    public function handle($rsmi, $stockId)
    {
        $srcFile = public_path('templates/rpci_template.xlsx');
        $spreadsheet = IOFactory::load($srcFile);

        // ✅ pick sheet by name (if sheet names are stock IDs)
        $sheet = $spreadsheet->getSheetByName((string) $stockId);
        // or by index: $sheet = $spreadsheet->getSheet((int) $stockId);

        $templateRow = $sheet->getHighestRow(); // last row is the "template"
        $highestCol  = $sheet->getHighestColumn(); // e.g. "K"

        foreach ($rsmi as $requisition) {
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
                    $formula = $srcCell->getValue(); // e.g. "=J59+G60-H60"

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

            $sheet->setCellValue("E{$targetRow}", Carbon::parse($requisition->updated_at)->format('m/d/Y'));

            $sheet->setCellValue("F{$targetRow}", $requisition->ris);
            $sheet->setCellValue("H{$targetRow}", $requisition->items->first()->requested_qty);

            // keep row height
            $sheet->getRowDimension($targetRow)
                ->setRowHeight($sheet->getRowDimension($templateRow)->getRowHeight());
        }

        $newFileName = 'rsmi_' . now()->format('Y-m-d_His') . '.xls';
        $out = storage_path('app/public/rpci/' . $newFileName);
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($out);

        return;
    }
}
