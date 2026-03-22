<?php

namespace Holiq\ActionData\Foundation\DataTransferObject;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\TreeMapper;
use Holiq\ActionData\Exceptions\InvalidArgumentException;
use Holiq\ActionData\Exceptions\MappingException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

trait HasResolvable
{
    /**
     * Resolve unstructured data from polymorphism types.
     *
     * Accepts a {@see FormRequest}, an Eloquent {@see Model}, or an `array`.
     * Passing `null` or any other type will throw an {@see InvalidArgumentException}
     * with a clear, descriptive message.
     *
     * @param  mixed  $abstract  The data source (FormRequest, Model, or array)
     *
     * @throws MappingException
     * @throws InvalidArgumentException
     */
    public static function resolveFrom(mixed $abstract): static
    {
        if ($abstract === null) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cannot resolve %s from null. Expected a FormRequest, Model, or array.',
                    static::class,
                ),
            );
        }

        if ($abstract instanceof FormRequest) {
            return static::resolveFromFormRequest(request: $abstract);
        }

        if ($abstract instanceof Model) {
            return static::resolveFromModel(model: $abstract);
        }

        if (is_array($abstract)) {
            /** @var array<array-key, mixed> $arrayData */
            $arrayData = $abstract;

            return static::resolve(data: $arrayData);
        }

        throw new InvalidArgumentException(
            sprintf(
                'Unsupported data type for %s resolution. Expected FormRequest, Model, or array — got: %s.',
                static::class,
                get_debug_type($abstract),
            ),
        );
    }

    /**
     * Resolve unstructured data from array.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param  array<TKey, TValue>  $data
     *
     * @throws MappingException
     */
    public static function resolve(array $data): static
    {
        $data = static::applyTransforms($data);

        try {
            /** @var static $instance */
            $instance = static::mapper()
                ->map(signature: static::class, source: static::resolveTheArrayKeyForm(data: $data));

            return $instance;
        } catch (MappingError $e) {
            throw MappingException::fromMappingError($e);
        }
    }

    /**
     * Get the mapper instance
     */
    protected static function mapper(): TreeMapper
    {
        return MapperRegistry::getMapper();
    }

    /**
     * Apply data transformations
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param  array<TKey, TValue>  $data
     * @return array<TKey, TValue>
     */
    protected static function applyTransforms(array $data): array
    {
        $transforms = static::transforms();

        foreach ($transforms as $key => $transform) {
            // Try exact key match first
            if (array_key_exists($key, $data)) {
                $data[$key] = $transform($data[$key]);

                continue;
            }

            // Try snake_case version of the key (for camelCase transform keys)
            $snakeKey = Str::snake($key);
            if ($snakeKey !== $key && array_key_exists($snakeKey, $data)) {
                $data[$snakeKey] = $transform($data[$snakeKey]);

                continue;
            }

            // Try camelCase version of the key (for snake_case transform keys)
            $camelKey = Str::camel($key);
            if ($camelKey !== $key && array_key_exists($camelKey, $data)) {
                $data[$camelKey] = $transform($data[$camelKey]);
            }
        }

        return $data;
    }

    /**
     * Define data transformations
     *
     * @return array<string, callable>
     */
    protected static function transforms(): array
    {
        return [];
    }

    /**
     * Resolve unstructured data from FormRequest
     *
     * @throws MappingError
     */
    public static function resolveFromFormRequest(FormRequest $request): static
    {
        /** @var array<array-key, mixed> $validatedData */
        $validatedData = $request->validated();

        return static::resolve($validatedData);
    }

    /**
     * Resolve unstructured data from Model
     *
     * @throws MappingError
     */
    public static function resolveFromModel(Model $model): static
    {
        /** @var array<array-key, mixed> $modelData */
        $modelData = $model->toArray();

        return static::resolve($modelData);
    }

    /**
     * Resolve all array key form according the config
     *
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    protected static function resolveTheArrayKeyForm(array $data): array
    {
        $array = [];

        foreach ($data as $key => $value) {
            $key = static::resolveArrayKeyOfInput(key: $key);

            if (is_array(value: $value)) {
                $valueContainsArray = $value;

                $array[$key] = static::resolveTheArrayKeyForm(data: $valueContainsArray);

                continue;
            }

            $array[$key] = $value;
        }

        return $array;
    }

    /**
     * Resolve the input of array key to constructor naming
     */
    protected static function resolveArrayKeyOfInput(string $key): string
    {
        return Str::camel(value: $key);
    }
}
