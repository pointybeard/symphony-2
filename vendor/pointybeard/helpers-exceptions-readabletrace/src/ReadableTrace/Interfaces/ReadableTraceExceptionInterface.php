<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Exceptions\ReadableTrace\Interfaces;

interface ReadableTraceExceptionInterface
{
    public function getReadableTrace(): ?string;
}
