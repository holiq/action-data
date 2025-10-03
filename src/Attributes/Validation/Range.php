<?php

namespace Holiq\ActionData\Attributes\Validation;

use Attribute;
use Holiq\ActionData\Contracts\Validator;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Range implements Validator
{
    public function __construct(
        public int | float $min,
        public int | float $max,
        public string $message = 'The :property field must be between :min and :max.',
    ) {
    }

    public function validate(mixed $value, string $property): bool
    {
        if (! is_numeric($value)) {
            return false;
        }

        $numericValue = is_string($value) ? (float) $value : $value;

        return $numericValue >= $this->min && $numericValue <= $this->max;
    }

    public function getErrorMessage(string $property): string
    {
        return str_replace(
            [':property', ':min', ':max'],
            [$property, (string) $this->min, (string) $this->max],
            $this->message
        );
    }
}
