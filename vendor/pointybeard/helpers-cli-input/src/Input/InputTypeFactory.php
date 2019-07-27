<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input;

use pointybeard\Helpers\Foundation\Factory;

final class InputTypeFactory extends Factory\AbstractFactory
{
    public function getTemplateNamespace(): string
    {
        return __NAMESPACE__.'\\Types\\%s';
    }

    public function getExpectedClassType(): ?string
    {
        return __NAMESPACE__.'\\Interfaces\\InputTypeInterface';
    }
}
