<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input\Exceptions;

class InputNotFoundException extends \Exception
{
    public function __construct(string $name, $code = 0, \Exception $previous = null)
    {
        return parent::__construct(sprintf('Input %s could not be found.', $name), $code, $previous);
    }
}
