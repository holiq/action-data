<?php

namespace Holiq\ActionData\Attributes\Validation;

use Attribute;
use Holiq\ActionData\Contracts\Validator;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Email implements Validator
{
    public function __construct(
        public string $message = 'The :property field must be a valid email address.',
    ) {
    }

    public function validate(mixed $value, string $property): bool
    {
        if ($value === null) {
            return true;
        }

        return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function getErrorMessage(string $property): string
    {
        return str_replace(':property', $property, $this->message);
    }
}
