<?php

namespace App\Services\Afms;

use App\Services\Print\PrPrintService;

/**
 * @deprecated Use App\Services\Print\PrPrintService directly.
 *
 * Kept as an alias so any code that still injects RequestService
 * continues to work without changes. Remove once all callsites
 * have been updated to PrPrintService.
 */
class RequestService extends PrPrintService
{
}
