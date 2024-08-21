<?php

namespace Holiq\ActionData\Commands\Concerns;

use Holiq\ActionData\Actions\Command\ResolveCommandAction;
use Holiq\ActionData\Contracts\Console;
use Holiq\ActionData\Exceptions\FileAlreadyExistException;
use Holiq\ActionData\Support\Source;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;

/**
 * @mixin Console
 */
trait InteractsWithConsole
{
    /**
     * @throws FileNotFoundException
     * @throws FileAlreadyExistException
     */
    public function handle(): void
    {
        $this->beforeCreate();

        ResolveCommandAction::resolve(parameters: ['console' => $this])->execute();

        $this->afterCreate();
    }

    public function getNamespacePath(): string
    {
        return Source::resolveNamespacePath(
            namespace: $this->getNamespace(),
        );
    }

    public function getFileName(): string
    {
        return $this->getClassName() . '.php';
    }

    public function getClassName(): string
    {
        return Str::of($this->resolveNameArgument())->classBasename()->trim();
    }

    public function getFullPath(): string
    {
        return $this->getNamespacePath() . '/' . $this->getFileName();
    }

    public function afterCreate(): void
    {
        //
    }

    public function beforeCreate(): void
    {
        //
    }
}
