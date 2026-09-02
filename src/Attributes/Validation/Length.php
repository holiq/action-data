<?php

namespace Holiq\ActionData\Attributes\Validation;

use Attribute;
use Holiq\ActionData\Contracts\Validator;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Length implements Validator
{
    public function __construct(
        public ?int $min = null,
        public ?int $max = null,
        public string $message = 'The :property field length is invalid.',
    ) {
    }

    public function validate(mixed $value, string $property): bool
    {
        if ($value === null) {
            return true;
        }

        if (! is_string($value)) {
            return false;
        }

        $length = mb_strlen($value);

        if ($this->min !== null && $length < $this->min) {
            return false;
        }

        if ($this->max !== null && $length > $this->max) {
            return false;
        }

        return true;
    }

    public function getErrorMessage(string $property): string
    {
        $message = $this->message !== 'The :property field length is invalid.'
            ? $this->message
            : null;

        if ($message === null && $this->min !== null && $this->max !== null) {
            $message = 'The :property field must be between :min and :max characters.';
        }

        if ($message === null && $this->min !== null) {
            $message = 'The :property field must be at least :min characters.';
        }

        if ($message === null && $this->max !== null) {
            $message = 'The :property field must not exceed :max characters.';
        }

        return str_replace(
            [':property', ':min', ':max'],
            [$property, (string) $this->min, (string) $this->max],
            $message ?? 'The :property field length is invalid.',
        );
    }
}
