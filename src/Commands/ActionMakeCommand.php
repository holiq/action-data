<?php

namespace Holiq\ActionData\Commands;

use Holiq\ActionData\Actions\Filesystem\FilePresentAction;
use Holiq\ActionData\Commands\Concerns\HasArguments;
use Holiq\ActionData\Commands\Concerns\HasOptions;
use Holiq\ActionData\Commands\Concerns\InteractsWithConsole;
use Holiq\ActionData\Contracts\Console;
use Holiq\ActionData\DataTransferObjects\Filesystem\FilePresentData;
use Holiq\ActionData\DataTransferObjects\NamespaceData;
use Holiq\ActionData\DataTransferObjects\PlaceholderData;
use Holiq\ActionData\Support\Source;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ActionMakeCommand extends Command implements Console
{
    use HasArguments, HasOptions, InteractsWithConsole;

    protected $signature = 'make:action {name} {--with-dto=} {--force}';

    protected $description = 'Create a new action';

    public function beforeCreate(): void
    {
        $this->info(string: 'Generating action file to your project');

        $this->ensureFilesAreAvailable();
    }

    public function afterCreate(): void
    {
        if ($this->resolveWithDtoOption()) {
            Artisan::call(command: 'make:dto', parameters: [
                'name' => $this->resolveWithDtoOption(),
                '--force' => $this->resolveForceOption(),
            ]);
        }
        $this->info(string: 'Successfully generated action file');
    }

    public function getNamespace(): string
    {
        return Source::resolveNamespace(
            data: new NamespaceData(
                structures: Source::resolveActionPath(),
                nameArgument: $this->resolveNameArgument(),
            ),
        );
    }

    /**
     * Check all files involved in an action + DTO generation before writing
     * either file. The actual generation still performs its normal checks.
     */
    protected function ensureFilesAreAvailable(): void
    {
        $dtoName = $this->resolveWithDtoOption();

        if ($this->resolveForceOption()) {
            return;
        }

        $filePresentAction = FilePresentAction::resolve();

        $filePresentAction->execute(
            data: new FilePresentData(
                fileName: $this->getFileName(),
                namespacePath: $this->getNamespacePath(),
            ),
        );

        if (! $dtoName) {
            return;
        }

        $filePresentAction->execute(
            data: new FilePresentData(
                fileName: basename($dtoName) . '.php',
                namespacePath: Source::resolveNamespacePath(
                    namespace: Source::resolveNamespace(
                        data: new NamespaceData(
                            structures: Source::resolveDataTransferObjectPath(),
                            nameArgument: $dtoName,
                        ),
                    ),
                ),
            ),
        );
    }

    public function getStubPath(): string
    {
        $stub = $this->resolveWithDtoOption() ? 'action-dto' : 'action';

        return Source::resolveStubForPath(name: $stub);
    }

    public function resolvePlaceholders(): PlaceholderData
    {
        return new PlaceholderData(
            namespace: $this->getNamespace(),
            class: $this->getClassName(),
            importClass: $this->resolveWithImportClass(),
            classBasename: basename((string) $this->resolveWithDtoOption()),
        );
    }

    public function resolveWithImportClass(): ?string
    {
        if (! $this->resolveWithDtoOption()) {
            return null;
        }

        return Source::resolveNamespace(
            data: new NamespaceData(
                structures: Source::resolveDataTransferObjectPath(),
                nameArgument: (string) $this->resolveWithDtoOption(),
                endsWith: (string) $this->resolveWithDtoOption(),
            ),
        );
    }
}
