<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input\Interfaces;

use pointybeard\Helpers\Cli\Input;

interface InputValidatorInterface
{
    public function validate(Input\AbstractInputType $input, Input\AbstractInputHandler $context);
}
