<?php

namespace Holiq\ActionData\Contracts;

use Holiq\ActionData\Foundation\DataTransferObject;

/**
 * Contract for validation attributes that need access to the complete DTO.
 */
interface ContextualValidator
{
    public function validateWithContext(
        mixed $value,
        string $property,
        DataTransferObject $data,
    ): bool;

    /**
     * Get the human-readable error message using the public error key
     * (snake_case).
     */
    public function getErrorMessage(string $property): string;
}
