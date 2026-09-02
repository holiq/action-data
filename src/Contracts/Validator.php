<?php

namespace Holiq\ActionData\Contracts;

use Holiq\ActionData\Foundation\DataTransferObject;

/**
 * Contract that every validation attribute must implement.
 *
 * To create a custom validation attribute, create a PHP Attribute class that
 * implements this interface. The attribute will then be automatically discovered
 * by {@see DataTransferObject::validateAttributes()}.
 *
 * ## Example: custom `#[Positive]` attribute
 *
 * ```php
 * use Attribute;
 * use Holiq\ActionData\Contracts\Validator;
 *
 * #[Attribute(Attribute::TARGET_PROPERTY)]
 * class Positive implements Validator
 * {
 *     public function validate(mixed $value, string $property): bool
 *     {
 *         return is_numeric($value) && $value > 0;
 *     }
 *
 *     public function getErrorMessage(string $property): string
 *     {
 *         return "'{$property}' must be a positive number.";
 *     }
 * }
 * ```
 *
 * Then use it on any DTO property:
 *
 * ```php
 * readonly class ProductData extends DataTransferObject
 * {
 *     public function __construct(
 *         #[Positive]
 *         public float $price,
 *     ) {}
 * }
 *
 * ProductData::resolve(['price' => -5])->validateAttributes();
 * // throws ValidationException: 'price' must be a positive number.
 * ```
 */
interface Validator
{
    /**
     * Validate a value for a specific property.
     *
     * @param  mixed  $value  The current value of the property
     * @param  string  $property  The property name (camelCase)
     * @return bool Return true if valid, false to fail validation
     */
    public function validate(mixed $value, string $property): bool;

    /**
     * Get the human-readable error message when validation fails.
     *
     * @param  string  $property  The public error key (snake_case)
     */
    public function getErrorMessage(string $property): string;
}
