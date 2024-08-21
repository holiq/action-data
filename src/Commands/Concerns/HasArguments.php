<?php

namespace Holiq\ActionData\Commands\Concerns;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * @mixin Command
 */
trait HasArguments
{
    public function resolveNameArgument(): string
    {
        /** @var string $argument */
        $argument = $this->argument(key: 'name');

        return Str::ucfirst(string: $argument);
    }
}
