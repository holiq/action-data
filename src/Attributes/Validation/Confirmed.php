<?php

namespace Holiq\ActionData\Attributes\Validation;

use Attribute;
use Holiq\ActionData\Contracts\ContextualValidator;
use Holiq\ActionData\Foundation\DataTransferObject;
use Illuminate\Support\Str;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Confirmed implements ContextualValidator
{
    public function __construct(
        public ?string $confirmationProperty = null,
        public string $message = 'The :property confirmation does not match.',
    ) {
    }

    public function validateWithContext(
        mixed $value,
        string $property,
        DataTransferObject $data,
    ): bool {
        $confirmationProperty = $this->confirmationProperty
            ? Str::camel($this->confirmationProperty)
            : Str::camel($property . '_confirmation');

        return $data->has($confirmationProperty)
            && $value === $data->get($confirmationProperty);
    }

    public function getErrorMessage(string $property): string
    {
        return str_replace(':property', $property, $this->message);
    }
}
