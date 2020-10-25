<?php

//declare(strict_types=1);

namespace Symphony\Symphony\Exceptions;

use pointybeard\Helpers\Exceptions\ReadableTrace;

class SymphonyException extends ReadableTrace\ReadableTraceException
{
    public function getReadableTrace(string $format = '[{{PATH}}/{{FILENAME}}:{{LINE}}] {{CLASS}}{{TYPE}}{{FUNCTION}}();'): ?string
    {
        // The trace of any previous exception that is an instance of
        // ReadableTraceException is likely to be much more informative
        // so use that intead
        if ($this->getPrevious() instanceof \pointybeard\Helpers\Exceptions\ReadableTrace\ReadableTraceException) {
            return $this->getPrevious()->getReadableTrace();
        }

        // Otherwise, default to whatever trace we have
        return parent::getReadableTrace();
    }
}
