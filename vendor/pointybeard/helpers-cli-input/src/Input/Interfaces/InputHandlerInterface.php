<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input\Interfaces;

use pointybeard\Helpers\Cli\Input;

interface InputHandlerInterface
{
    public function bind(Input\InputCollection $inputCollection, ?int $flags = null): bool;

    public function validate(?int $flags = null): void;

    public function find(string $name);

    public function getInput(): array;

    public function getCollection(): ?Input\InputCollection;
}
