<?php

namespace Holiq\ActionData\Foundation\DataTransferObject;

use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;

class MapperRegistry
{
    private static ?TreeMapper $mapperInstance = null;

    /**
     * Get the TreeMapper instance
     */
    public static function getMapper(): TreeMapper
    {
        return self::$mapperInstance ??= (new MapperBuilder())->mapper();
    }

    /**
     * Reset the TreeMapper instance
     */
    public static function resetMapper(): void
    {
        self::$mapperInstance = null;
    }
}
