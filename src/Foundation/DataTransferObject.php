<?php

namespace Holiq\ActionData\Foundation;

use Holiq\ActionData\Contracts\Validator;
use Holiq\ActionData\Foundation\DataTransferObject\HasResolvable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Tappable;
use Spatie\Cloneable\Cloneable;

abstract readonly class DataTransferObject
{
    use Cloneable;
    use HasResolvable;
    use Tappable;

    /**
     * Properties to exclude when creating records
     *
     * @return array<string>
     */
    protected function toExcludedPropertiesOnCreate(): array
    {
        return [];
    }

    /**
     * Properties to exclude when updating records
     *
     * @return array<string>
     */
    protected function toExcludedPropertiesOnUpdate(): array
    {
        return [];
    }

    /**
     * Convert the DTO to an array with snake_case keys
     *
     * This method converts all property names from camelCase to snake_case
     * to match Laravel's database column naming conventions.
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
                fn ($value, $key): array => [$this->resolveArrayKey($key) => $value]
            )
            ->toArray();
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

        return Collection::make($this->toArray())
            ->except($excluded)
            ->toArray();
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

        return Collection::make($this->toArray())
            ->except($excluded)
            ->toArray();
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
     * Convert DTO to JSON
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
     * Validate the DTO using a custom validation callback
     *
     * @param  callable(static): bool  $validator
     *
     * @throws \InvalidArgumentException
     */
    public function validate(callable $validator, string $message = 'DTO validation failed'): static
    {
        if (! $validator($this)) {
            throw new \InvalidArgumentException($message);
        }

        return $this;
    }

    /**
     * Validate the DTO using attributes on properties
     *
     * @throws \InvalidArgumentException
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
                        $errors[] = $attributeInstance->getErrorMessage($propertyName);
                    }
                }
            }
        }

        if (! empty($errors)) {
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errors));
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

        $property->setAccessible(true);

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
