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
     * Resolve unstructured data from a FormRequest, Model, or array.
     *
     * @param  mixed  $abstract  The data source
     * @param  list<string>  $preserveNestedKeys  Top-level array properties
     *                                            whose child keys must remain unchanged
     *
     * @throws MappingException
     * @throws InvalidArgumentException
     */
    public static function resolveFrom(
        mixed $abstract,
        bool $strict = true,
        array $preserveNestedKeys = [],
    ): static {
        if ($abstract === null) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cannot resolve %s from null. Expected a FormRequest, Model, or array.',
                    static::class,
                ),
            );
        }

        if ($abstract instanceof FormRequest) {
            return static::resolveFromFormRequest(
                request: $abstract,
                strict: $strict,
                preserveNestedKeys: $preserveNestedKeys,
            );
        }

        if ($abstract instanceof Model) {
            return static::resolveFromModel(
                model: $abstract,
                strict: $strict,
                preserveNestedKeys: $preserveNestedKeys,
            );
        }

        if (is_array($abstract)) {
            /** @var array<array-key, mixed> $arrayData */
            $arrayData = $abstract;

            return static::resolve(
                data: $arrayData,
                strict: $strict,
                preserveNestedKeys: $preserveNestedKeys,
            );
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
     * Resolve unstructured data from an array.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param  array<TKey, TValue>  $data
     * @param  list<string>  $preserveNestedKeys  Top-level array properties
     *                                            whose child keys must remain unchanged
     *
     * @throws MappingException
     */
    public static function resolve(
        array $data,
        bool $strict = true,
        array $preserveNestedKeys = [],
    ): static {
        $data = static::applyTransforms($data);

        try {
            /** @var static $instance */
            $instance = static::mapperFor(strict: $strict)
                ->map(
                    signature: static::class,
                    source: static::resolveTheArrayKeyForm(
                        data: $data,
                        preserveNestedKeys: $preserveNestedKeys,
                    ),
                );

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
     * Resolve the mapper for the requested input strictness.
     *
     * Keeping the original mapper() signature preserves custom mapper
     * overrides while allowing permissive mapping as an explicit opt-in.
     */
    protected static function mapperFor(bool $strict): TreeMapper
    {
        return $strict
            ? static::mapper()
            : MapperRegistry::getMapper(strict: false);
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
     * Resolve unstructured data from a FormRequest.
     *
     * @param  list<string>  $preserveNestedKeys
     *
     * @throws MappingException
     */
    public static function resolveFromFormRequest(
        FormRequest $request,
        bool $strict = true,
        array $preserveNestedKeys = [],
    ): static {
        /** @var array<array-key, mixed> $validatedData */
        $validatedData = $request->validated();

        return static::resolve(
            data: $validatedData,
            strict: $strict,
            preserveNestedKeys: $preserveNestedKeys,
        );
    }

    /**
     * Resolve unstructured data from a Model.
     *
     * @param  list<string>  $preserveNestedKeys
     *
     * @throws MappingException
     */
    public static function resolveFromModel(
        Model $model,
        bool $strict = true,
        array $preserveNestedKeys = [],
    ): static {
        /** @var array<array-key, mixed> $modelData */
        $modelData = $model->toArray();

        return static::resolve(
            data: $modelData,
            strict: $strict,
            preserveNestedKeys: $preserveNestedKeys,
        );
    }

    /**
     * Resolve all array key form according the config
     *
     * @param  array<array-key, mixed>  $data
     * @param  list<string>  $preserveNestedKeys  Top-level array properties
     *                                            whose child keys must remain unchanged
     * @return array<array-key, mixed>
     */
    protected static function resolveTheArrayKeyForm(
        array $data,
        array $preserveNestedKeys = [],
        bool $isRoot = true,
    ): array {
        $preserveNestedKeys = array_map(
            static fn (string $key): string => Str::camel($key),
            $preserveNestedKeys,
        );
        $array = [];

        foreach ($data as $key => $value) {
            $resolvedKey = is_string($key)
                ? static::resolveArrayKeyOfInput(key: $key)
                : $key;

            if (is_array(value: $value)) {
                $preserveChildren = $isRoot
                    && is_string($resolvedKey)
                    && in_array($resolvedKey, $preserveNestedKeys, true);

                $array[$resolvedKey] = $preserveChildren
                    ? $value
                    : static::resolveTheArrayKeyForm(
                        data: $value,
                        preserveNestedKeys: $preserveNestedKeys,
                        isRoot: false,
                    );

                continue;
            }

            $array[$resolvedKey] = $value;
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
