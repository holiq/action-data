<?php

namespace Holiq\ActionData\Foundation\DataTransferObject;

use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;

class MapperRegistry
{
    private static ?TreeMapper $strictMapper = null;

    private static ?TreeMapper $permissiveMapper = null;

    /**
     * Get a cached TreeMapper instance.
     *
     * Valinor is strict by default. The permissive mapper is opt-in and
     * allows source arrays to contain keys that are not constructor fields.
     */
    public static function getMapper(bool $strict = true): TreeMapper
    {
        if ($strict) {
            return self::$strictMapper ??= (new MapperBuilder())->mapper();
        }

        return self::$permissiveMapper ??= (new MapperBuilder())
            ->allowSuperfluousKeys()
            ->mapper();
    }

    /** Reset all cached TreeMapper instances. */
    public static function resetMapper(): void
    {
        self::$strictMapper = null;
        self::$permissiveMapper = null;
    }
}
