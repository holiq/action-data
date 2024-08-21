<?php

namespace Holiq\ActionData;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class ActionDataServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPublishers();

        $this->commands(commands: $this->actionDataCommands());
    }

    public function register(): void
    {
        $this->mergeConfigFrom(path: __DIR__ . '/../config/action-data.php', key: 'action-data');
    }

    protected function registerPublishers(): void
    {
        $this->publishes([
            __DIR__ . '/../config/action-data.php' => config_path(path: 'action-data.php'),
        ], groups: 'config');
    }

    /**
     * @return array<string>
     */
    public function actionDataCommands(): array
    {
        $commandPath = __DIR__ . '/Commands';

        $fileSystem = new Filesystem();

        $commandFiles = $fileSystem->files(directory: $commandPath);

        return Arr::map(
            array: $commandFiles,
            callback: fn (SplFileInfo $file) => $this->resolveCommandNamespace(file: $file)
        );
    }

    protected function resolveCommandNamespace(SplFileInfo $file, ?string $directory = null): string
    {
        return Str::of(string: $file->getFilenameWithoutExtension())
            ->prepend(values: $directory ? "$directory\\" : '')
            ->prepend(values: __NAMESPACE__ . '\\Commands\\');
    }
}
