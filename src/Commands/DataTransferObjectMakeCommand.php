<?php

namespace Holiq\ActionData\Commands;

use Holiq\ActionData\Commands\Concerns\HasArguments;
use Holiq\ActionData\Commands\Concerns\HasOptions;
use Holiq\ActionData\Commands\Concerns\InteractsWithConsole;
use Holiq\ActionData\Contracts\Console;
use Holiq\ActionData\DataTransferObjects\NamespaceData;
use Holiq\ActionData\DataTransferObjects\PlaceholderData;
use Holiq\ActionData\Support\Source;
use Illuminate\Console\Command;

class DataTransferObjectMakeCommand extends Command implements Console
{
    use HasArguments, HasOptions, InteractsWithConsole;

    protected $signature = 'make:dto {name} {--force}';

    protected $description = 'Create a new dto';

    public function beforeCreate(): void
    {
        $this->info(string: 'Generating action file to your project');
    }

    public function afterCreate(): void
    {
        $this->info(string: 'Successfully generate action file');
    }

    public function getNamespace(): string
    {
        return Source::resolveNamespace(
            data: new NamespaceData(
                structures: Source::resolveDataTransferObjectPath(),
                nameArgument: $this->resolveNameArgument(),
            )
        );
    }

    public function getStubPath(): string
    {
        return Source::resolveStubForPath(name: 'data-transfer-object');
    }

    public function resolvePlaceholders(): PlaceholderData
    {
        return new PlaceholderData(
            namespace: $this->getNamespace(),
            class: $this->getClassName(),
        );
    }
}
