<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Foundation\Factory;

/**
 * Used to keep a record of all dynamically created classes.
 */
class ClassRegistry implements Interfaces\ClassRegistryInterface
{
    private static $register = [];

    private function __construct()
    {
    }

    public static function register(string $name, ...$properties): void
    {
        if (in_array($name, self::$register)) {
            throw new \Exception(sprintf('Class %s has already been registered', $name));
        }
        self::$register[$name] = $properties;
    }

    public static function lookup(string $name): ?array
    {
        return self::$register[$name] ?? null;
    }
}
