<?php

namespace App\Services\Print;

/**
 * Contract for all print services (PR, PO, etc.)
 *
 * handle() accepts:
 *   $template     — the xlsx filename (e.g. 'pr_meal_template.xlsx')
 *   $blocks       — array of block/flat data to render into the sheet
 *   $sourceRecord — the Eloquent model that owns the print (PurchaseRequest, PurchaseOrder, etc.)
 *
 * Returns the saved filename so the caller can stream it as a download.
 */
interface PrintService
{
    public function handle(string $template, array $blocks, $sourceRecord): string;
}
