<?php

namespace Holiq\ActionData\Actions\Filesystem;

use Holiq\ActionData\DataTransferObjects\Filesystem\FilePresentData;
use Holiq\ActionData\Exceptions\FileAlreadyExistException;
use Holiq\ActionData\Foundation\Action;
use Illuminate\Filesystem\Filesystem;

readonly class FilePresentAction extends Action
{
    /**
     * @throws FileAlreadyExistException
     */
    public function execute(FilePresentData $data, bool $withForce = false): bool
    {
        $filesystem = new Filesystem;

        $path = $data->namespacePath . '/' . $data->fileName;

        if ($withForce) {
            return $filesystem->delete(paths: $path);
        }

        if ($filesystem->exists(path: $path)) {
            return throw new FileAlreadyExistException($data->fileName);
        }

        return false;
    }
}
