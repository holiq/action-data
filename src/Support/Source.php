<?php

namespace Holiq\ActionData\Support;

use Holiq\ActionData\DataTransferObjects\NamespaceData;
use Illuminate\Support\Str;

class Source
{
    /**
     * Get the configured path for Actions
     */
    public static function resolveActionPath(): string
    {
        /** @var string $result */
        $result = config(key: 'action-data.action_path', default: 'app/Actions');

        return $result;
    }

    /**
     * Get the configured path for Data Transfer Objects
     */
    public static function resolveDataTransferObjectPath(): string
    {
        /** @var string $result */
        $result = config(key: 'action-data.data_path', default: 'app/DataTransferObjects');

        return $result;
    }

    /**
     * Resolve namespace from NamespaceData
     */
    public static function resolveNamespace(NamespaceData $data): string
    {
        /** @var string $result */
        $result = Str::replace(
            search: '/',
            replace: '\\',
            subject: static::resolveNamespaceDir(
                data: $data,
                namespace: Str::ucfirst($data->structures)
            ),
        );

        return $result;
    }

    /**
     * Resolve namespace directory based on data structure and name argument
     */
    public static function resolveNamespaceDir(NamespaceData $data, string $namespace): string
    {
        return Str::contains(haystack: $data->nameArgument, needles: '/') ?
            Str::of(string: '/')
                ->start(prefix: $namespace)
                ->finish(cap: dirname($data->nameArgument))
            : $namespace;
    }

    /**
     * Resolve absolute path from namespace
     */
    public static function resolveNamespacePath(string $namespace): string
    {
        return base_path(
            path: static::transformNamespaceToPath(namespace: $namespace),
        );
    }

    /**
     * Transform namespace to file system path
     */
    public static function transformNamespaceToPath(string $namespace): string
    {
        /** @var string $result */
        $result = Str::of(string: $namespace)->replace(
            search: '\\',
            replace: '/',
        )->lcfirst();

        return $result;
    }

    /**
     * Get the absolute path to a stub file
     *
     * @param  string  $name  The stub name (without .stub extension)
     */
    public static function resolveStubForPath(string $name): string
    {
        /** @var string $result */
        $result = Str::replace(search: ':name', replace: $name, subject: __DIR__ . '/../../stubs/:name.stub');

        return $result;
    }
}
