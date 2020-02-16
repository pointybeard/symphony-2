<?php

declare(strict_types=1);

namespace Symphony\Symphony\Interfaces;

interface XSLTProcessorInterface {
    public static function isXSLTProcessorAvailable(): bool;

    public function process(
        ?string $xml = null,
        ?string $xsl = null,
        array $parameters = [],
        array $registerFunctions = []
    );

    public function validate(string $xsd, ?string $xml = null): bool;
    public function isErrors(): bool;
    public function getError(bool $all = false, bool $rewind = false): ?array;
}
