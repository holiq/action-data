<?php

namespace Holiq\ActionData\Exceptions;

use Illuminate\Http\Response;

class InvalidArgumentException extends \InvalidArgumentException
{
    public function __construct(string $message, int $code = Response::HTTP_BAD_REQUEST, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
