<?php

namespace Holiq\ActionData\DataTransferObjects;

use Holiq\ActionData\Foundation\DataTransferObject;
use Illuminate\Support\Str;

readonly class PlaceholderData extends DataTransferObject
{
    final public function __construct(
        public ?string $namespace = null,
        public ?string $class = null,
        public ?string $subject = null,
    ) {
    }

    protected function resolveArrayKey(string $key): string
    {
        return Str::camel($key);
    }
}
