<?php

namespace App\Domain\Gasu\Exceptions;

use RuntimeException;

class RequisitionLockedException extends RuntimeException
{
    public static function completed(string $requisitionUuid): self
    {
        return new self("Requisition {$requisitionUuid} has already been issued (completed); its items can no longer change.");
    }

    public static function deleted(string $requisitionUuid): self
    {
        return new self("Requisition {$requisitionUuid} has been deleted.");
    }
}
