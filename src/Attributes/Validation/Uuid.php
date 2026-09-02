<?php

namespace Holiq\ActionData\Attributes\Validation;

use Attribute;
use Holiq\ActionData\Contracts\Validator;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Uuid implements Validator
{
    public function __construct(
        public string $message = 'The :property field must be a valid UUID.',
    ) {
    }

    public function validate(mixed $value, string $property): bool
    {
        if ($value === null) {
            return true;
        }

        return is_string($value)
            && preg_match(
                '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
                $value,
            ) === 1;
    }

    public function getErrorMessage(string $property): string
    {
        return str_replace(':property', $property, $this->message);
    }
}
