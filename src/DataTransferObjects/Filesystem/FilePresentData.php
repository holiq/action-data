<?php

namespace Holiq\ActionData\DataTransferObjects\Filesystem;

use Holiq\ActionData\Foundation\DataTransferObject;

readonly class FilePresentData extends DataTransferObject
{
    final public function __construct(
        public string $fileName,
        public string $namespacePath,
    ) {
    }
}
