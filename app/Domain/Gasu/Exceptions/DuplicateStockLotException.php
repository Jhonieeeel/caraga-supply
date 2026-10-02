<?php

namespace App\Domain\Gasu\Exceptions;

use RuntimeException;

class DuplicateStockLotException extends RuntimeException
{
    public static function forSupply(string $supplyUuid, string $stockNumber): self
    {
        return new self("Stock number '{$stockNumber}' already exists for supply {$supplyUuid}.");
    }
}
