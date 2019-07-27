<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input\Interfaces;

interface InputTypeInterface
{
    const FLAG_REQUIRED = 0x0001;
    const FLAG_OPTIONAL = 0x0002;
    const FLAG_VALUE_REQUIRED = 0x0004;
    const FLAG_VALUE_OPTIONAL = 0x0008;

    const FLAG_TYPE_STRING = 0x0100;
    const FLAG_TYPE_INT = 0x0200;
    const FLAG_TYPE_INCREMENTING = 0x0400;

    public function getType(): string;

    public function respondsTo(string $name): bool;

    public function __toString(): string;

    public function getDisplayName(): string;
}
