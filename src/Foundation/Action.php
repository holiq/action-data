<?php

namespace Holiq\ActionData\Foundation;

abstract readonly class Action
{
    /**
     * Resolve an action instance from Laravel's container
     *
     * This method uses Laravel's dependency injection container to create
     * an instance of the action, allowing for automatic injection of dependencies.
     *
     * @param  array<array-key, mixed>  $parameters  Additional parameters for dependency injection
     * @return static The resolved action instance
     */
    public static function resolve(array $parameters = []): static
    {
        /**
         * @var static $instance
         */
        $instance = resolve(name: static::class, parameters: $parameters);

        return $instance;
    }
}
