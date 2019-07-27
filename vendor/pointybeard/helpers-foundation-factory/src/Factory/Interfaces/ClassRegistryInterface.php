<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Foundation\Factory\Interfaces;

interface ClassRegistryInterface
{
    public static function register(string $name, ...$properties): void;
    public static function lookup(string $name): ?array;
}
