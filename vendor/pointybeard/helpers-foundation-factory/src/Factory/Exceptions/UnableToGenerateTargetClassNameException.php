<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Foundation\Factory\Exceptions;

class UnableToGenerateTargetClassNameException extends \Exception
{
    public function __construct(string $caller, string $message, int $code = 0, \Exception $previous = null)
    {
        return parent::__construct(sprintf('Unable to generate target class name from values provided. Called by %s. Returned: %s', $caller, $message), $code, $previous);
    }
}
