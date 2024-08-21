<?php

namespace Holiq\ActionData\Actions\Stub;

use Holiq\ActionData\DataTransferObjects\PlaceholderData;
use Holiq\ActionData\Foundation\Action;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @template TKey of array-key
 * @template TValue
 */
readonly class ReplacePlaceholderAction extends Action
{
    /**
     * Replace Placeholder Stub data
     *
     *
     * @throws FileNotFoundException
     */
    public function execute(string $filePath, PlaceholderData $placeholders): void
    {
        $filesystem = new Filesystem;

        $resolved = $placeholders->toArray();

        $stub = $filesystem->get(path: $filePath);

        Collection::make($resolved)
            ->filter()
            ->each(
                function ($value, $key) use (&$stub) {
                    /**
                     * @var string $replacement
                     * @var string $placeholder
                     */
                    [$replacement, $placeholder] = [$value, $key];

                    $stub = Str::replace(
                        search: "{{ $placeholder }}",
                        replace: $replacement,
                        subject: $stub
                    );
                }
            );

        /** @var string $contents */
        $contents = $stub;

        $filesystem->ensureDirectoryExists(path: Str::of($filePath)->beforeLast(search: '/'));

        $filesystem->put(path: $filePath, contents: $contents);
    }
}
