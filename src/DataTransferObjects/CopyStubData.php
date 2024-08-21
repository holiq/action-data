<?php

namespace Holiq\ActionData\DataTransferObjects;

use Holiq\ActionData\Foundation\DataTransferObject;

readonly class CopyStubData extends DataTransferObject
{
    final public function __construct(
        public string $stubPath,
        public string $targetPath,
        public string $fileName,
        public PlaceholderData $placeholders,
    ) {
    }
}
