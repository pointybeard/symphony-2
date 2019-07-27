<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input\Exceptions;

use pointybeard\Helpers\Cli\Input;

class RequiredInputMissingException extends \Exception
{
    private $input;

    public function __construct(Input\AbstractInputType $input, $code = 0, \Exception $previous = null)
    {
        $this->input = $input;

        return parent::__construct(sprintf(
            'missing %s',
            $input->getDisplayName()
        ), $code, $previous);
    }

    public function getInput(): Input\AbstractInputType
    {
        return $this->input;
    }
}
