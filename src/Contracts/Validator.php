<?php

namespace Holiq\ActionData\Contracts;

interface Validator
{
    /**
     * Validate a value for a specific property
     */
    public function validate(mixed $value, string $property): bool;

    /**
     * Get the error message for a property
     */
    public function getErrorMessage(string $property): string;
}
