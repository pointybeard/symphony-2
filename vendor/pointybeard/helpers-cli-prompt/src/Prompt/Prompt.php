<?php
namespace pointybeard\Helpers\Cli\Prompt;

use pointybeard\Helpers\Functions\Flags;
use pointybeard\Helpers\Functions\Cli;
use pointybeard\Helpers\Cli\Message;

class Prompt
{
    const FLAG_SILENT = 0x001;

    private $prompt = null;
    private $flags = null;
    private $default = null;
    private $validator = null;
    private $character = null;
    private $target = null;

    public function __get($name)
    {
        return $this->$name;
    }

    public function flags($flags)
    {
        $this->flags = $flags;
        return $this;
    }

    public function validator(\Closure $validator)
    {
        $this->validator = $validator;
        return $this;
    }

    public function target($target)
    {
        $this->target = $target;
        return $this;
    }

    public function default($default)
    {
        $this->default = $default;
        return $this;
    }

    public function character($character)
    {
        $this->character = $character;
        return $this;
    }

    public function prompt($prompt)
    {
        $this->prompt = !($prompt instanceof Message\Message)
            ? (new Message\Message)
                ->message($prompt)
                ->flags(null)
            : $prompt
        ;
        return $this;
    }

    /**
     * @param  mixed $prompt        either a string or instance of Message\Message
     * @param  int $flags
     * @param  string $default      optional default value if no input is supplied
     * @param  Closure $validator   input can be passed through this function
     *                              for validation. It must return a boolean.
     * @param  string $character    character to display between after the prompt
     * @param  stream $target       target to read input from. Default is STDIN
     */
    public function __construct($prompt = null, $flags = null, $default = null, \Closure $validator = null, $character = ":", $target = STDIN)
    {
        if (!is_null($prompt)) {
            $this->prompt($prompt);
        }

        if (!is_null($validator)) {
            $this->validator($validator);
        }

        $this
            ->flags($flags)
            ->default($default)
            ->character($character)
            ->target($target)
        ;
    }

    /**
     * Waits for input from $target (default is STDIN). Supports
     * silent input by setting $flag to Prompt::FLAG_SILENT however this
     * requires bash. If bash is not available, then it will trigger an
     * E_USER_NOTICE error and unset FLAG_SILENT.
     *
     * Credit to Troels Knak-Nielsen for inspiring this method.
     * (http://www.sitepoint.com/interactive-cli-password-prompt-in-php/)
     *
     */
    public function display()
    {

        $silent = Flags\is_flag_set($this->flags, self::FLAG_SILENT);

        if ($silent == true) {
            if(!Cli\can_invoke_bash()) {
               trigger_error("bash cannot be invoked from PHP so FLAG_SILENT cannot be used", E_USER_NOTICE);
               $silent = false;

           } elseif ($this->target != STDIN) {
               throw new \Exception("Can only use FLAG_SILENT if target is STDIN");
            }
        }

        // Include the default value and promp character
        $this->prompt->message(sprintf(
            "%s%s%s ",
            $this->prompt->message,
            (!is_null($this->default) ? " [{$this->default}]" : null),
            $this->character
        ));

        do {
            $this->prompt->display();

            if ($silent == true) {
                $input = shell_exec("/usr/bin/env bash -c 'read -s in && echo \$in'");
                echo PHP_EOL;

            } else {
                $input = fgets($this->target, 256);
            }

            $input = trim($input);

            if (strlen($input) <= 0 && !is_null($this->default)) {
                $input = (string)$this->default;
            }

        // Keep asking for input if validation fails
        } while ($this->validator instanceof \Closure && !($this->validator)($input));

        return $input;
    }
}
