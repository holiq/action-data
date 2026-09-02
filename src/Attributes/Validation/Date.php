<?php

namespace Holiq\ActionData\Attributes\Validation;

use Attribute;
use DateTimeImmutable;
use DateTimeInterface;
use Holiq\ActionData\Contracts\Validator;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Date implements Validator
{
    public function __construct(
        public ?string $format = null,
        public string $message = 'The :property field must be a valid date.',
    ) {
    }

    public function validate(mixed $value, string $property): bool
    {
        if ($value === null) {
            return true;
        }

        if ($value instanceof DateTimeInterface) {
            return true;
        }

        if (! is_string($value) || trim($value) === '') {
            return false;
        }

        if ($this->format === null) {
            try {
                new DateTimeImmutable($value);

                return true;
            } catch (\Throwable) {
                return false;
            }
        }

        $date = DateTimeImmutable::createFromFormat($this->format, $value);
        $errors = DateTimeImmutable::getLastErrors();

        return $date !== false
            && ($errors === false
                || ($errors['warning_count'] === 0 && $errors['error_count'] === 0));
    }

    public function getErrorMessage(string $property): string
    {
        return str_replace(
            [':property', ':format'],
            [$property, $this->format ?? 'any valid date format'],
            $this->message,
        );
    }
}
