<?php

namespace Holiq\ActionData\Support;

use Holiq\ActionData\DataTransferObjects\NamespaceData;
use Holiq\ActionData\Exceptions\InvalidArgumentException;
use Illuminate\Support\Str;

class Source
{
    /**
     * Normalize and validate a relative class name used by a generator.
     *
     * Class names may contain forward-slash separated namespace segments, but
     * must not escape the configured generation directory.
     */
    public static function normalizeClassName(string $name): string
    {
        if ($name === '' || str_starts_with($name, '/') || str_ends_with($name, '/') || str_contains($name, '\\')) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid class name "%s". Use a relative class name with optional "/" namespace separators.',
                    $name,
                ),
            );
        }

        $segments = explode('/', $name);
        $normalizedSegments = [];

        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new InvalidArgumentException(
                    sprintf('Invalid class name "%s".', $name),
                );
            }

            $normalizedSegment = Str::studly($segment);

            if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $normalizedSegment)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Invalid class name "%s". Each segment must resolve to a valid PHP class name.',
                        $name,
                    ),
                );
            }

            $normalizedSegments[] = $normalizedSegment;
        }

        return implode('/', $normalizedSegments);
    }

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
        $namespace = static::resolveNamespaceDir(
            data: $data,
            namespace: Str::ucfirst($data->structures)
        );
        /** @var string $result */
        $result = Str::of($namespace)
            ->finish(cap: $data->endsWith ? '/' . basename($data->endsWith) : '')
            ->replace(search: '/', replace: '\\');

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
