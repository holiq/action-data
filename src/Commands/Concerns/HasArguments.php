<?php

namespace Holiq\ActionData\Commands\Concerns;

use Holiq\ActionData\Support\Source;
use Illuminate\Console\Command;

/**
 * @mixin Command
 */
trait HasArguments
{
    public function resolveNameArgument(): string
    {
        /** @var string $argument */
        $argument = $this->argument(key: 'name');

        return Source::normalizeClassName($argument);
    }
}
