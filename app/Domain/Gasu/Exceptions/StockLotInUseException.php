<?php

namespace App\Domain\Gasu\Exceptions;

use RuntimeException;

class StockLotInUseException extends RuntimeException
{
    public static function unknown(string $stockNumber): self
    {
        return new self("Stock number '{$stockNumber}' does not exist for this supply.");
    }

    public static function hasAllocations(string $stockNumber): self
    {
        return new self("Stock number '{$stockNumber}' has already been requested in a requisition and cannot be deleted.");
    }
}
