<?php

namespace App\Services\Print;

use Carbon\Carbon;

class PrPrintService extends BasePrintService
{
    // ==================== PR-SPECIFIC COMMON FIELDS ====================

    protected function fillCommonFields($purchaseRequest): void
    {
        $user = auth()->user();
        $proc = $purchaseRequest->procurement ?? null;
        $emp = $proc?->employee ?? null;
        $empUser = optional($emp?->user ?? null);

        $placeholders = $this->config['placeholders'];

        $this->bulkReplace([
            $placeholders['project_title'] ?? '{{project_title}}' => $proc?->project_title ?? 'N/A',
            $placeholders['user_name'] ?? '{{user_name}}' => $user?->name ?? $empUser->name ?? 'N/A',
            $placeholders['user_designation'] ?? '{{user_designation}}' => $user?->designation ?? $empUser->designation ?? 'N/A',
            $placeholders['section'] ?? '{{section}}' => optional($emp?->section ?? null)->name ?? ($proc?->pmo_end_user ?? 'N/A'),
            $placeholders['pr_number'] ?? '{{pr_number}}' => $purchaseRequest->pr_number ?? 'N/A',
            $placeholders['input_date'] ?? '{{input_date}}' => 'Date: ' . ($purchaseRequest->input_date
                ? Carbon::parse($purchaseRequest->input_date)->format('m-d-y')
                : 'N/A'),
            $placeholders['chargeability'] ?? '{{chargeability}}' => $purchaseRequest->chargeability ?? 'N/A',
        ]);
    }
}

