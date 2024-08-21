<?php

namespace Holiq\ActionData\Commands\Concerns;

use Illuminate\Console\Command;

/**
 * @mixin Command
 */
trait HasOptions
{
    public function resolveForceOption(): bool
    {
        return (bool) $this->option(key: 'force');
    }
}
