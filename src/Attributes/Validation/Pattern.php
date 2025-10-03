<?php

namespace Holiq\ActionData\Attributes\Validation;

use Attribute;
use Holiq\ActionData\Contracts\Validator;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Pattern implements Validator
{
    public function __construct(
        public string $regex,
        public string $message = 'The :property field format is invalid.',
    ) {
    }

    public function validate(mixed $value, string $property): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return preg_match($this->regex, $value) === 1;
    }

    public function getErrorMessage(string $property): string
    {
        return str_replace(':property', $property, $this->message);
    }
}
