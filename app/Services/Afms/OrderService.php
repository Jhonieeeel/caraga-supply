<?php

namespace App\Services\Afms;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class OrderService {
    public function handle($template, $data) {
        $po_template = public_path('templates/' . $template .'');

        $sheet = IOFactory::load($po_template);
        $activeSheet = $sheet->getActiveSheet();

        $row = 22;
        $lastRow = 25;
        foreach($data as $po) {
            $activeSheet->insertNewRowBefore($row, 1);
            $activeSheet->setCellValue("B{$row}", $po['unit']);
            $activeSheet->setCellValue("C{$row}", $po['pax_qty']);
            $activeSheet->setCellValue("D{$row}", $po['mealSnack']);
            $activeSheet->setCellValue("E{$row}", $po['arrangement']);
            $activeSheet->setCellValue("F{$row}", $po['delivery_date']);
            $activeSheet->setCellValue("G{$row}", $po['menu']);
            $activeSheet->setCellValue("J{$row}",  $po['qty'] ?? 0);
            $activeSheet->setCellValue("K{$row}", 0);
            $row ++;
        }

        $directory = storage_path('app/public/po-records');
        $writer = new Xlsx($sheet);
        $newFileName = 'PO_MEAL' . now()->format('Y-m-d_His') . '.xlsx';
        $outputPath = $directory . DIRECTORY_SEPARATOR . $newFileName;
        $writer->save($outputPath);

        return $newFileName;

    }
}
