<?php

namespace Holiq\ActionData\Actions\Command;

use Closure;
use Holiq\ActionData\Actions\Filesystem\FilePresentAction;
use Holiq\ActionData\Actions\Stub\CopyStubAction;
use Holiq\ActionData\Contracts\Console;
use Holiq\ActionData\DataTransferObjects\CopyStubData;
use Holiq\ActionData\DataTransferObjects\Filesystem\FilePresentData;
use Holiq\ActionData\Exceptions\FileAlreadyExistException;
use Holiq\ActionData\Foundation\Action;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

readonly class ResolveCommandAction extends Action
{
    public function __construct(
        protected Console $console,
        protected ?Closure $copyStubData = null,
    ) {
    }

    /**
     * @throws FileAlreadyExistException
     * @throws FileNotFoundException
     */
    public function execute(): void
    {
        FilePresentAction::resolve()
            ->execute(
                data: new FilePresentData(
                    fileName: $this->console->getFileName(),
                    namespacePath: $this->console->getNamespacePath(),
                ),
                withForce: $this->console->resolveForceOption(),
            );

        CopyStubAction::resolve()->execute(data: $this->getDefaultCopyStubData());
    }

    protected function getDefaultCopyStubData(): CopyStubData
    {
        return new CopyStubData(
            stubPath: $this->console->getStubPath(),
            targetPath: $this->console->getNamespacePath(),
            fileName: $this->console->getFileName(),
            placeholders: $this->console->resolvePlaceholders()
        );
    }
}
