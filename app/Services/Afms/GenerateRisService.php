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
        $docx->setValue('purpose', $requisition->purpose);
        $docx->setValue('requested_by', $requisition->requestedBy->name ?? '');
        $docx->setValue('approved_by', $requisition->approvedBy->name ?? '');
        $docx->setValue('issued_by', $requisition->issuedBy->name ?? '');
        $docx->setValue('received_by', $requisition->receivedBy->name ?? '');

        // user designation
        $docx->setValue('req_designation', $requisition->requestedBy->designation ?? '');
        $docx->setValue('approved_designation', $requisition->approvedBy->designation ?? '');
        $docx->setValue('issued_designation', $requisition->requestedBy->designation ?? '');
        $docx->setValue('received_designation', $requisition->receivedBy->designation ?? '');

        // dates
        $docx->setValue('requested_date', $requisition->requested_date ?? '');
        $docx->setValue('approved_date', $requisition->approved_date ?? '');
        $docx->setValue('issued_date', $requisition->issued_date ?? '');
        $docx->setValue('received_date', $requisition->received_date ?? '');

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

        return $outputFile;
    }
}
