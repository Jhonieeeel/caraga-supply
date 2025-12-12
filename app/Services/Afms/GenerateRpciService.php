<?php

namespace App\Services\Afms;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateRpciService
{
    public function handle(array $transactions)
    {
        // read file
        $srcFile = public_path('templates/rpci_template.xlsx');
        $spreadsheet = IOFactory::load($srcFile);

        $sheet = $spreadsheet->getActiveSheet();

        // duplicate or insert new row
        $rowNumber = 17;
        for ($index = 0; $index < count($transactions); $index++) {
            $sheet->insertNewRowBefore($rowNumber + $index + 1, 1);
        }

        // add data per row
        $articleNumber = 1;

        for ($index = $rowNumber; $index < $rowNumber + count($transactions); $index++) {
            $transaction = $transactions[$index - $rowNumber];

            // article col
            $sheet->setCellValue("C{$index}", $articleNumber);

            $sheetToCopy = $spreadsheet->getSheetByName('1');

            if ($sheetToCopy) {
                $newTitle = (string)$articleNumber;

                // Limit to 31 characters and replace invalid characters
                $newTitle = substr($newTitle, 0, 31);
                $newTitle = preg_replace('/[:\\\\\/\?\*\[\]]/', '_', $newTitle);


                // Make sure the title doesn't exist already
                $originalTitle = $newTitle;
                $i = 1;
                while ($spreadsheet->sheetNameExists($newTitle)) {
                    $newTitle = $originalTitle . '_' . $i;
                    $i++;
                }

                // Clone the sheet
                $newSheet = clone $sheetToCopy;

                // Set the new title
                $newSheet->setTitle($newTitle);

                // Add the cloned sheet
                $spreadsheet->addSheet($newSheet);
            }

            // name col
            $sheet->setCellValue("D{$index}", $transaction['stock_name']);
            $sheet->getCell("D{$index}")->getHyperlink()->setUrl("sheet://{$articleNumber}!A1");

            $cell = "D{$index}";
            $sheet->getStyle($cell)->applyFromArray([
            'font' => [
                    'color' => ['argb' => Color::COLOR_DARKBLUE],
                    'underline' => Font::UNDERLINE_SINGLE,
                ],
            ]);

            // stock number col
            $sheet->setCellValue("E{$index}", $transaction['stock_number']);

            // unit measure col
            $sheet->setCellValue("F{$index}", $transaction['unit_measure']);

            // unit value col
            $sheet->setCellValue("G{$index}", $transaction['unit_value']);

            // balance per card and on hand col ( MUST SAME VALUE0)
            $sheet->setCellValue("H{$index}", $transaction['net_quantity']);
            $sheet->setCellValue("I{$index}", $transaction['net_quantity']);

            $articleNumber ++;

        }

        // for card per row PO
        for ($index = 0; $index < count($transactions); $index++) {

            $transaction = $transactions[$index];

            // 1️⃣ Select sheet by name
            $sheetName = (string)($index + 1);
            $currentSheet = $spreadsheet->getSheetByName($sheetName);

            if (!$currentSheet) {
                Log::error("Sheet {$sheetName} not found!");
                continue;
            }

            $currentSheet->setCellValue("F4", $transaction['stock_name']);
            $currentSheet->setCellValue("K4", $transaction['stock_number']);


            $poTransactions = $transaction['po']['transactions'];
            $risTransactions = $transaction['ris']['transactions'];

            // Po Transactions
            $startRow = 10;
            foreach($poTransactions as $transaction) {

                // write data
                $date = Carbon::parse($transaction['created_at'])->format('Y-m-d');
                $currentSheet->setCellValue("F{$startRow}", $date);
                $currentSheet->setCellValue("F{$startRow}", $transaction['type_of_transaction']);
                $currentSheet->setCellValue("G{$startRow}", $transaction['quantity']);
                $currentSheet->setCellValue("J{$startRow}", $transaction['quantity']);

                $startRow ++;
            }

            // Ris Transactions
            foreach($risTransactions as $transaction) {

                // write data
                $date = Carbon::parse($transaction['created_at'])->format('Y-m-d');
                $currentSheet->setCellValue("F{$startRow}", $date);
                $currentSheet->setCellValue("F{$startRow}", $transaction['type_of_transaction']);
                $currentSheet->setCellValue("H{$startRow}", $transaction['quantity']);

                // calculation
                $currentSheet->setCellValue("J{$startRow}", "=J" . ($startRow - 1) . "+G{$startRow}-H{$startRow}");


                $startRow ++;

            }


        }





        // save process
        $directory = storage_path('app/public/rpci');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $newFileName = 'rpci_' . now()->format('Y-m-d_His') . '.xlsx';
        $outputPath = $directory . DIRECTORY_SEPARATOR . $newFileName;

        $writer->save($outputPath);

        return $newFileName;
    }
}
