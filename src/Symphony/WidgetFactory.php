<?php

declare(strict_types=1);

namespace Symphony\Symphony;

use pointybeard\Helpers\Foundation\Factory;

class WidgetFactory extends Factory\AbstractFactory
{
    public function getTemplateNamespace(): string
    {
        return __NAMESPACE__.'\\Widgets\\%s';
    }

    public function getExpectedClassType(): ?string
    {
        return __NAMESPACE__.'\\AbstractWidget';
    }
}
