<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Exceptions\ReadableTrace;

use pointybeard\Helpers\Functions\Debug;

class ReadableTraceException extends \Exception implements Interfaces\ReadableTraceExceptionInterface
{
    public function getReadableTrace(string $format = '[{{PATH}}/{{FILENAME}}:{{LINE}}] {{CLASS}}{{TYPE}}{{FUNCTION}}();'): ?string
    {
        return Debug\readable_debug_backtrace($this->getTrace(), $format);
    }
}
