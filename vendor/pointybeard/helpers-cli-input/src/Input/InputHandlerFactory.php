<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input;

use pointybeard\Helpers\Functions\Flags;
use pointybeard\Helpers\Foundation\Factory;

final class InputHandlerFactory extends Factory\AbstractFactory
{
    public function getTemplateNamespace(): string
    {
        return __NAMESPACE__.'\\Handlers\\%s';
    }

    public function getExpectedClassType(): ?string
    {
        return __NAMESPACE__.'\\Interfaces\\InputHandlerInterface';
    }

    public static function build(string $name, ...$arguments): object
    {

        // Since passing flags is optional, we can use array_pad
        // to ensure there are always at least 2 elements in $arguments
        [$collection, $flags] = array_pad($arguments, 2, null);

        $factory = new self;

        try {
            $handler = $factory->instanciate(
                $factory->generateTargetClassName($name)
            );
        } catch (\Exception $ex) {
            throw new Exceptions\UnableToLoadInputHandlerException($name, 0, $ex);
        }

        if (null !== $collection) {
            $handler->bind(
                $collection,
                $flags
            );
        }

        return $handler;
    }
}
