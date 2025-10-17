<?php

namespace App\Services\Afms;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ReadRpciService
{
    public function handle($stockNumber)
    {
        $spreadsheet = IOFactory::load(public_path('templates/rpci_template.xlsx'));
        $sheet = $spreadsheet->getActiveSheet();


        $rpciData = [];

        foreach ($sheet->getRowIterator(17) as $row) {
            $rowIndex = $row->getRowIndex();

            $stock = $sheet->getCell("E{$rowIndex}")->getValue();

            // Supplies-2025-01

            if ($stock == $stockNumber) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true);

                $values = [];

                $data = [];
                foreach ($cellIterator as $cell) {

                    if ($cell->isFormula()) {
                        $values[] = $cell->getCalculatedValue();
                        continue;
                    }
                    $values[] = $cell->getValue();
                }

                foreach ($values as $val) {
                    if ($val === null) {
                        continue;
                    }

                    $data[] = $val;
                }

                $rpciData[] = $data;

                Log::info('Row found for stock number ' . $stockNumber, $rpciData);


                return $data;
            }
        }

        return null;
    }
}
