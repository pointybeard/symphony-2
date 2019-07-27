<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input\Types;

use pointybeard\Helpers\Functions\Flags;

class Flag extends Option
{
    public function __construct(string $name = null, int $flags = null, string $description = null, object $validator = null, $default = false)
    {
        if (Flags\is_flag_set($flags, self::FLAG_VALUE_REQUIRED) || Flags\is_flag_set($flags, self::FLAG_VALUE_OPTIONAL)) {
            throw new \Exception('The flags FLAG_VALUE_REQUIRED and FLAG_VALUE_OPTIONAL cannot be used on an input of type Flag');
        }
        parent::__construct($name, null, $flags, $description, $validator, $default);
    }
}
