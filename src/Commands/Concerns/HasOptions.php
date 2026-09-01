<?php

namespace Holiq\ActionData\Commands\Concerns;

use Holiq\ActionData\Support\Source;
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

    public function resolveWithDtoOption(): string
    {
        /** @var string|null $option */
        $option = $this->option(key: 'with-dto');

        throw_if(
            condition: is_null($option) && $this->input->hasParameterOption('--with-dto'),
            exception: new \InvalidArgumentException(message: 'The --with-dto option must be a string.')
        );

        if ($option === null) {
            return '';
        }

        return Source::normalizeClassName((string) $option);
    }
}
