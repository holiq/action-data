<?php

namespace Holiq\ActionData\Actions\Stub;

use Holiq\ActionData\DataTransferObjects\CopyStubData;
use Holiq\ActionData\Foundation\Action;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;

readonly class CopyStubAction extends Action
{
    /**
     * Copy Stub to Diamond Infrastructure
     *
     *
     * @throws FileNotFoundException
     */
    public function execute(CopyStubData $data): void
    {
        $absolutePath = $data->targetPath . '/' . $data->fileName;

        $filesystem = new Filesystem;

        $filesystem->ensureDirectoryExists(path: $data->targetPath);

        $filesystem->copy(
            path: $data->stubPath,
            target: $absolutePath,
        );

        ReplacePlaceholderAction::resolve()
            ->execute($absolutePath, $data->placeholders);
    }
}
