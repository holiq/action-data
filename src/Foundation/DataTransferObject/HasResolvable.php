<?php

namespace Holiq\ActionData\Foundation\DataTransferObject;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use Holiq\ActionData\Exceptions\InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

trait HasResolvable
{
    /**
     * Resolve unstructured data from polymorphism types
     *
     * @param  mixed  $abstract  The data source (FormRequest, Model, or array)
     *
     * @throws MappingError
     * @throws InvalidArgumentException
     */
    public static function resolveFrom(mixed $abstract): static
    {
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
            'Unsupported data type for DTO resolution. Expected FormRequest, Model, or array, got: ' . get_debug_type($abstract)
        );
    }

    /**
     * Resolve unstructured data from array
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param  array<TKey, TValue>  $data
     *
     * @throws MappingError
     */
    public static function resolve(array $data): static
    {
        /** @var static $instance */
        $instance = (new MapperBuilder())
            ->mapper()
            ->map(signature: static::class, source: static::resolveTheArrayKeyForm(data: $data));

        return $instance;
    }

    /**
     * Resolve unstructured data from array
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param  array<TKey, TValue>  $data
     *
     * @throws MappingError
     *
     * @deprecated can use resolve()
     */
    public static function resolveFromArray(array $data): static
    {
        return static::resolve($data);
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
     * @template TArrayKey of array-key
     * @template TArrayValue
     *
     * @param  array<TArrayKey, TArrayValue>  $data
     * @return array<TArrayKey, mixed>
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
