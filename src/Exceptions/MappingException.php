<?php

namespace Holiq\ActionData\Exceptions;

use CuyZ\Valinor\Mapper\MappingError;
use Illuminate\Http\Response;

/**
 * Thrown when a DTO cannot be resolved from the given data.
 *
 * This exception wraps the internal {@see MappingError} from cuyz/valinor so
 * that consumers of this package are decoupled from that dependency. If you
 * need to inspect the root cause, use {@see \Throwable::getPrevious()}.
 *
 * Example:
 *
 * ```php
 * try {
 *     $data = MyData::resolve($input);
 * } catch (MappingException $e) {
 *     // $e->getMessage()  — human-readable summary
 *     // $e->getPrevious() — original MappingError for deeper inspection
 * }
 * ```
 */
class MappingException extends \RuntimeException
{
    public function __construct(
        string $message,
        int $code = Response::HTTP_UNPROCESSABLE_ENTITY,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create a MappingException from a valinor MappingError.
     *
     * Extracts a readable message from all mapping errors and
     * preserves the original MappingError as the exception cause.
     */
    public static function fromMappingError(MappingError $error): self
    {
        $messages = [];

        foreach ($error->messages() as $message) {
            $messages[] = (string) $message;
        }

        $summary = ! empty($messages)
            ? implode(', ', $messages)
            : 'Failed to map data to ' . $error->type();

        return new self(
            message: 'Resolution failed: ' . $summary,
            previous: $error,
        );
    }
}
