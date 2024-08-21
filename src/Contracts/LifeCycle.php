<?php

namespace Holiq\ActionData\Contracts;

interface LifeCycle
{
    public function beforeCreate(): void;

    public function afterCreate(): void;
}
