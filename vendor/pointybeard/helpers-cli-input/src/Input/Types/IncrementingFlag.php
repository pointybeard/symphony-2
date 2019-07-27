<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input\Types;

use pointybeard\Helpers\Functions\Flags;

class IncrementingFlag extends Flag
{
    public function __construct(string $name = null, int $flags = null, string $description = null, object $validator = null, $default = 0)
    {
        if (Flags\is_flag_set($flags, self::FLAG_TYPE_INCREMENTING)) {
            $flags = $flags | self::FLAG_TYPE_INCREMENTING;
        }
        parent::__construct($name, null, $flags, $description, $validator, $default);
    }
}
