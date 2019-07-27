<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Foundation\Factory\Interfaces;

interface FactoryInterface
{
    public function getTemplateNamespace(): string;

    public function getExpectedClassType(): ?string;
}
