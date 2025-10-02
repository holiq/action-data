<?php

namespace Holiq\ActionData\Exceptions;

use Illuminate\Http\Response;

class MappingException extends \RuntimeException
{
    public function __construct(string $message, int $code = Response::HTTP_UNPROCESSABLE_ENTITY, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
