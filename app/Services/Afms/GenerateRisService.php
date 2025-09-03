<?php

namespace App\Services\Afms;

use PhpOffice\PhpWord\TemplateProcessor;

class GenerateRisService
{
    public function handle($requisition)
    {
        $docx = new TemplateProcessor(public_path("templates/ris_template.docx"));

        // fields
        $docx->setValue('division', '');
        $docx->setValue('responsibility_code', '');
        $docx->setValue('office', '');
        $docx->setValue('ris', $requisition->ris ?? '');
        $docx->setValue('purpose', '');
        $docx->setValue('requested_by', $requisition->requestedBy->name ?? '');
        $docx->setValue('approved_by', $requisition->approvedBy->name ?? '');
        $docx->setValue('issued_by', $requisition->issuedBy->name ?? '');
        $docx->setValue('received_by', $requisition->receivedBy->name ?? '');

        $data = [];

        foreach ($requisition->items as $item) {
            $data[] = [
                'stock_no' => $item->stock->barcode ?? '',
                'unit' => $item->stock->supply->unit ?? '',
                'item' => $item->stock->supply->name ?? '',
                'quantity' => $item->requested_qty ?? '',
                'yes' => '✓',
                'no'  => '',
                'remarks' => $item->remarks ?? '',
            ];
        }

        $docx->cloneRowAndSetValues('stock_no', $data);

        $fileName = $requisition->ris . '.docx';

        // ris docx and save
        $outputFile = storage_path('app/public/' . $fileName);
        $docx->saveAs($outputFile);

        logger()->info('Done Generate Word', [
            'id' => $requisition->id,
        ]);

        return $outputFile;
    }
}
