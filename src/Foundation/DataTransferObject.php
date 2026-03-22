<?php

namespace Holiq\ActionData\Foundation;

use Holiq\ActionData\Contracts\Validator;
use Holiq\ActionData\Exceptions\InvalidArgumentException;
use Holiq\ActionData\Foundation\DataTransferObject\HasResolvable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Tappable;
use Illuminate\Validation\ValidationException;
use Spatie\Cloneable\Cloneable;

abstract readonly class DataTransferObject
{
    use Cloneable {
        with as private cloneWith;
    }
    use HasResolvable;
    use Tappable;

    /**
     * List of property keys to exclude when calling {@see toArrayForCreate()}.
     *
     * Keys must be in **snake_case** — matching the output keys of {@see toArray()}.
     * If your DTO property is named `$createdAt` (camelCase), the exclusion key
     * is `'created_at'` (snake_case).
     *
     * Example:
     *
     * ```php
     * protected function toExcludedPropertiesOnCreate(): array
     * {
     *     return ['created_at', 'updated_at']; // snake_case keys
     * }
     * ```
     *
     * @return list<string> snake_case property keys to exclude
     */
    protected function toExcludedPropertiesOnCreate(): array
    {
        return [];
    }

    /**
     * List of property keys to exclude when calling {@see toArrayForUpdate()}.
     *
     * Keys must be in **snake_case** — matching the output keys of {@see toArray()}.
     * If your DTO property is named `$password` (camelCase), the exclusion key
     * is `'password'` (same, since no conversion needed for single-word names).
     *
     * Example:
     *
     * ```php
     * protected function toExcludedPropertiesOnUpdate(): array
     * {
     *     return ['password', 'email_verified_at']; // snake_case keys
     * }
     * ```
     *
     * @return list<string> snake_case property keys to exclude
     */
    protected function toExcludedPropertiesOnUpdate(): array
    {
        return [];
    }

    /**
     * Convert the DTO to an array with snake_case keys.
     *
     * This method converts all property names from camelCase to snake_case
     * to match Laravel's database column naming conventions.
     *
     * Nested {@see DataTransferObject} instances are recursively converted to
     * arrays as well. Arrays of DTOs are also handled transparently.
     *
     * @return array<array-key, mixed>
     */
    public function toArray(): array
    {
        $excludedPropertyKeys = [
            "\x00*\x00excludedPropertiesOnCreate",
            "\x00*\x00excludedPropertiesOnUpdate",
            "\x00*\x00resolveArrayKeyUsing",
        ];

        return Collection::wrap((array) $this)
            ->except(keys: $excludedPropertyKeys)
            ->mapWithKeys(
                fn ($value, $key): array => [
                    $this->resolveArrayKey($key) => $this->resolveNestedValue($value),
                ],
            )
            ->toArray();
    }

    /**
     * Recursively resolve a property value:
     * - A nested DTO is converted to an array via its own toArray()
     * - An array is scanned for DTO items and each is converted
     * - Any other value is returned as-is
     */
    private function resolveNestedValue(mixed $value): mixed
    {
        if ($value instanceof DataTransferObject) {
            return $value->toArray();
        }

        if (is_array($value)) {
            $resolved = [];

            foreach ($value as $key => $item) {
                $resolved[$key] = $this->resolveNestedValue($item);
            }

            return $resolved;
        }

        return $value;
    }

    /**
     * Convert the DTO to an array for creating records
     *
     * This method excludes properties defined in toExcludedPropertiesOnCreate()
     *
     * @return array<array-key, mixed>
     */
    public function toArrayForCreate(): array
    {
        $excluded = $this->toExcludedPropertiesOnCreate();

        return Collection::make($this->toArray())->except($excluded)->toArray();
    }

    /**
     * Convert the DTO to an array for updating records
     *
     * This method excludes properties defined in toExcludedPropertiesOnUpdate()
     *
     * @return array<array-key, mixed>
     */
    public function toArrayForUpdate(): array
    {
        $excluded = $this->toExcludedPropertiesOnUpdate();

        return Collection::make($this->toArray())->except($excluded)->toArray();
    }

    /**
     * Resolve the array key from property name to database column format
     */
    protected function resolveArrayKey(string $key): string
    {
        return Str::snake(value: $key);
    }

    /**
     * Check if the DTO has a specific property
     */
    public function has(string $property): bool
    {
        return property_exists($this, $property);
    }

    /**
     * Get a specific property value
     */
    public function get(string $property, mixed $default = null): mixed
    {
        return $this->has($property) ? $this->{$property} : $default;
    }

    /**
     * Create a new instance with specific properties overridden.
     *
     * Uses named arguments for an expressive, IDE-friendly API:
     *
     *   $updated = $dto->with(email: 'new@example.com', name: 'Jane');
     *
     * All property names passed must exist on the DTO, otherwise an
     * InvalidArgumentException is thrown before any cloning takes place.
     *
     * @throws InvalidArgumentException when an unknown property name is passed
     */
    public function with(mixed ...$values): static
    {
        foreach (array_keys($values) as $property) {
            if (! property_exists($this, (string) $property)) {
                throw new InvalidArgumentException(
                    sprintf(
                        "Property '%s' does not exist on %s.",
                        $property,
                        static::class,
                    ),
                );
            }
        }

        return $this->cloneWith(...$values);
    }

    /**
     * Create a new instance with overridden values from an associative array.
     *
     * Accepts both snake_case and camelCase keys — they are automatically
     * normalised to the camelCase property names used by the DTO constructor:
     *
     *   $updated = $dto->withArray(['first_name' => 'Jane', 'email' => 'jane@example.com']);
     *   $updated = $dto->withArray(['firstName' => 'Jane', 'email' => 'jane@example.com']);
     *
     * @param  array<string, mixed>  $overrides
     *
     * @throws InvalidArgumentException when an unknown property key is passed
     */
    public function withArray(array $overrides): static
    {
        $normalized = [];

        foreach ($overrides as $key => $value) {
            $normalized[Str::camel($key)] = $value;
        }

        foreach (array_keys($normalized) as $property) {
            if (! property_exists($this, $property)) {
                throw new InvalidArgumentException(
                    sprintf(
                        "Property '%s' does not exist on %s.",
                        $property,
                        static::class,
                    ),
                );
            }
        }

        return $this->cloneWith(...$normalized);
    }

    /**
     * Create a new instance with the specified nullable properties set to null.
     *
     * Useful for "clearing" optional fields without having to re-supply all
     * other property values:
     *
     *   $cleared = $dto->without('password', 'avatar');
     *
     * An InvalidArgumentException is thrown when:
     *   - a given property name does not exist on the DTO, or
     *   - the property is not nullable (it cannot legally hold null).
     *
     * @throws InvalidArgumentException when a property does not exist or is not nullable
     */
    public function without(string ...$properties): static
    {
        $reflection = new \ReflectionClass($this);
        $nullValues = [];

        foreach ($properties as $property) {
            if (! property_exists($this, $property)) {
                throw new InvalidArgumentException(
                    sprintf(
                        "Property '%s' does not exist on %s.",
                        $property,
                        static::class,
                    ),
                );
            }

            $type = $reflection->getProperty($property)->getType();
            $isNullable = $type === null || $type->allowsNull();

            if (! $isNullable) {
                throw new InvalidArgumentException(
                    sprintf(
                        "Property '%s' on %s is not nullable and cannot be cleared with without().",
                        $property,
                        static::class,
                    ),
                );
            }

            $nullValues[$property] = null;
        }

        return $this->cloneWith(...$nullValues);
    }

    /**
     * Convert DTO to snake_case JSON
     *
     * @throws \JsonException
     */
    public function toJson(int $options = 0): string
    {
        $json = json_encode($this->toArray(), $options);

        if ($json === false) {
            throw new \JsonException('Failed to encode DTO to JSON');
        }

        return $json;
    }

    /**
     * Convert DTO to camelCase JSON
     *
     * @throws \JsonException
     */
    public function toCamelJson(int $options = 0): string
    {
        $json = json_encode($this->toCamelCase(), $options);

        if ($json === false) {
            throw new \JsonException('Failed to encode DTO to JSON');
        }

        return $json;
    }

    /**
     * Convert DTO to camelCase array
     *
     * @return array<string, mixed>
     */
    public function toCamelCase(): array
    {
        return $this->transformKeys(fn (string $key) => Str::camel($key));
    }

    /**
     * Transform array keys using a callback
     *
     * @param  callable(string): string  $transformer
     * @return array<string, mixed>
     */
    protected function transformKeys(callable $transformer): array
    {
        $result = [];

        foreach ($this->toArray() as $key => $value) {
            $newKey = $transformer((string) $key);

            if (is_array($value)) {
                $result[$newKey] = $this->transformArrayKeys(
                    $value,
                    $transformer,
                );
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Recursively transform array keys
     *
     * @param  array<mixed, mixed>  $array
     * @param  callable(string): string  $transformer
     * @return array<int|string, mixed>
     */
    protected function transformArrayKeys(
        array $array,
        callable $transformer,
    ): array {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = is_string($key) ? $transformer($key) : $key;

            if (is_array($value)) {
                $result[$newKey] = $this->transformArrayKeys(
                    $value,
                    $transformer,
                );
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Validate the DTO using a custom validation callback
     *
     * @param  callable(static): bool  $validator
     *
     * @throws InvalidArgumentException
     */
    public function validate(
        callable $validator,
        string $message = 'DTO validation failed',
    ): static {
        if (! $validator($this)) {
            throw new InvalidArgumentException($message);
        }

        return $this;
    }

    /**
     * Validate the DTO using attributes on properties.
     *
     * On failure, throws an {@see ValidationException} with
     * a structured, per-field error map — the same format used by Laravel Form Requests.
     * This means Laravel's exception handler will automatically convert it to a
     * 422 JSON response without any extra code in your controller:
     *
     * ```json
     * {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name":  ["The name field is required."],
     *     "email": ["The email field must be a valid email address."]
     *   }
     * }
     * ```
     *
     * @throws ValidationException
     */
    public function validateAttributes(): static
    {
        $reflection = new \ReflectionClass($this);
        $errors = [];

        foreach ($reflection->getProperties() as $property) {
            $propertyName = $property->getName();
            $value = $this->getPropertyValue($property);

            foreach ($property->getAttributes() as $attribute) {
                $attributeInstance = $attribute->newInstance();

                // Check if it's a validation attribute
                if ($attributeInstance instanceof Validator) {
                    if (! $attributeInstance->validate($value, $propertyName)) {
                        $errors[$propertyName][] = $attributeInstance->getErrorMessage(
                            $propertyName,
                        );
                    }
                }
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return $this;
    }

    /**
     * Get property value safely
     */
    private function getPropertyValue(\ReflectionProperty $property): mixed
    {
        if (! $property->isInitialized($this)) {
            return null;
        }

        return $property->getValue($this);
    }

    /**
     * Die and dump the current DTO data for debugging
     */
    public function dd(): never
    {
        dd($this);
    }

    /**
     * Dump the current DTO data for debugging
     */
    public function dump(): static
    {
        dump($this);

        return $this;
    }
}
