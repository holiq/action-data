<?php

namespace Holiq\ActionData\Attributes\Validation;

use Attribute;
use Holiq\ActionData\Contracts\Validator;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class In implements Validator
{
    /**
     * @param  array<int, string|int|float|bool|null>  $values
     */
    public function __construct(
        public array $values,
        public string $message = 'The :property field must be one of: :values.',
    ) {
    }

    public function validate(mixed $value, string $property): bool
    {
        if ($value === null) {
            return true;
        }

        return in_array($value, $this->values, true);
    }

    public function getErrorMessage(string $property): string
    {
        return str_replace(
            [':property', ':values'],
            [
                $property,
                implode(
                    ', ',
                    array_map(
                        static fn (string | int | float | bool | null $value): string => (string) $value,
                        $this->values,
                    ),
                ),
            ],
            $this->message,
        );
    }
}
