<?php

namespace Holiq\ActionData\Contracts;

use Holiq\ActionData\DataTransferObjects\PlaceholderData;

interface Placeholders
{
    public function resolvePlaceholders(): PlaceholderData;
}
