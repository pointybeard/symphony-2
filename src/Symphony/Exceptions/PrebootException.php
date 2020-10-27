<?php

namespace Symphony\Symphony\Exceptions;

class PrebootException extends SymphonyException
{
    protected $script;

    public function __construct(string $script, string $message, $code = 0, \Exception $previous = null)
    {
        $this->script = $script;

        return parent::__construct(
            sprintf(
                'Pre-boot script %s failed to execute. Returned: %s',
                $this->script,
                $message
            ),
            $code,
            $previous
        );
    }

    public function getScript(): string
    {
        return $this->script;
    }
}
