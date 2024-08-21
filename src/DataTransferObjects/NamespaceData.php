<?php

namespace Holiq\ActionData\DataTransferObjects;

use Holiq\ActionData\Foundation\DataTransferObject;

readonly class NamespaceData extends DataTransferObject
{
    public function __construct(
        public string $structures,
        public string $nameArgument,
    ) {
    }
}
