<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input\Exceptions;

use pointybeard\Helpers\Cli\Input\AbstractInputType;

class InputValidationFailedException extends \Exception
{
    public function __construct(AbstractInputType $input, $code = 0, \Exception $previous = null)
    {
        return parent::__construct(sprintf('Validation failed for %s. Returned: %s', $input->getDisplayName(), $previous->getMessage()), $code, $previous);
    }
}
