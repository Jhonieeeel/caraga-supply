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
        $generatedAt = now();

        $report = 'For the month of ' . $start->format('F');

        $serialNo = $this->nextSerial($generatedAt);

        $activeSheet->setCellValue('B5', $report);
        $activeSheet->setCellValue('I7', $serialNo);
        $activeSheet->setCellValue('I8', Carbon::parse(now()->format('Y-m-d'))->format('F-j-Y'));

        $overallQuantity  = 0;
        $rowStart = 13;
        foreach ($rsmi as $item) {

            if ($item->type_of_transaction === "RIS") {
                // The requisition may have been deleted after it was issued.
                $activeSheet->setCellValue("B{$rowStart}", $item->requisition?->ris ?? 'N/A');
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
        // serial in the name keeps two reports generated in the same second apart
        $newFileName = 'rsmi_' . now()->format('Y-m-d_His') . '_' . $serialNo . '.xls';


        $relativePath = 'rsmi/' . $newFileName;
        $outputPath = storage_path('app/public/' . $relativePath);
        $transaction->rsmi_file = $relativePath;
        $transaction->rsmi_serial = $serialNo;
        $transaction->save();

        $writer->save($outputPath);

        return $relativePath;
    }

    /**
     * Supply-YYYY-MM-N where N is one more than the highest serial already
     * issued this month, so serials never repeat even when a report is
     * regenerated (which overwrites the same transaction's file). Reports
     * generated before rsmi_serial was stored only count towards the floor.
     */
    protected function nextSerial(Carbon $generatedAt): string
    {
        $prefix = 'Supply-' . $generatedAt->format('Y') . '-' . $generatedAt->format('m') . '-';

        $maxSeries = Transaction::where('rsmi_serial', 'like', $prefix . '%')
            ->pluck('rsmi_serial')
            ->map(fn (string $serial) => (int) substr($serial, strlen($prefix)))
            ->max() ?? 0;

        $legacyCount = Transaction::whereNotNull('rsmi_file')
            ->whereNull('rsmi_serial')
            ->whereYear('updated_at', $generatedAt->year)
            ->whereMonth('updated_at', $generatedAt->month)
            ->count();

        return $prefix . (max($maxSeries, $legacyCount) + 1);
    }
}
