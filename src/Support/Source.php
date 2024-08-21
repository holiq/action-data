<?php

namespace Holiq\ActionData\Support;

use Holiq\ActionData\DataTransferObjects\NamespaceData;
use Illuminate\Support\Str;

class Source
{
    public static function resolveActionPath(): string
    {
        /** @var string $result */
        $result = config(key: 'action-data.action_path');

        return $result;
    }

    public static function resolveDataTransferObjectPath(): string
    {
        /** @var string $result */
        $result = config(key: 'action-data.data_path');

        return $result;
    }

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

    public static function resolveNamespaceDir(NamespaceData $data, string $namespace): string
    {
        return Str::contains(haystack: $data->nameArgument, needles: '/') ?
            Str::of(string: '/')
                ->start(prefix: $namespace)
                ->finish(cap: dirname($data->nameArgument))
            : $namespace;
    }

    public static function resolveNamespacePath(string $namespace): string
    {
        return base_path(
            path: static::transformNamespaceToPath(namespace: $namespace),
        );
    }

    public static function transformNamespaceToPath(string $namespace): string
    {
        /** @var string $result */
        $result = Str::of(string: $namespace)->replace(
            search: '\\',
            replace: '/',
        )->lcfirst();

        return $result;
    }

    public static function resolveStubForPath(string $name): string
    {
        /** @var string $result */
        $result = Str::replace(search: ':name', replace: $name, subject: __DIR__ . '/../../stubs/:name.stub');

        return $result;
    }
}
