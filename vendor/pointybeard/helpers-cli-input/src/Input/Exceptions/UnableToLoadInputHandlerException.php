<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input\Exceptions;

class UnableToLoadInputHandlerException extends \Exception
{
    public function __construct(string $name, $code = 0, \Exception $previous = null)
    {
        return parent::__construct(sprintf('The input handler %s could not be loaded. Returned: %s', $name, $previous->getMessage()), $code, $previous);
    }
}
