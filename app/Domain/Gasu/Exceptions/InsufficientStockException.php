<?php

namespace App\Domain\Gasu\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public static function forSupply(string $supplyUuid, int $requested, int $available): self
    {
        return new self(
            "Cannot allocate {$requested} units for supply {$supplyUuid}: only {$available} available across all stock lots."
        );
    }
}
