<?php

namespace Holiq\ActionData\Attributes\Validation;

use Attribute;
use Holiq\ActionData\Contracts\Validator;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Required implements Validator
{
    public function __construct(
        public string $message = 'The :property field is required.',
    ) {
    }

    public function validate(mixed $value, string $property): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        if (is_array($value) && empty($value)) {
            return false;
        }

        return true;
    }

    public function getErrorMessage(string $property): string
    {
        return str_replace(':property', $property, $this->message);
    }
}
