<?php

namespace pointybeard\Helpers\Cli\Message;

use pointybeard\Helpers\Functions\Flags;
use pointybeard\Helpers\Cli\Colour;

class Message
{
    private $message = null;
    private $background = null;
    private $foreground = null;
    private $dateFormat = null;
    private $flags = null;

    const FLAG_NONE = null;
    const FLAG_PREPEND_DATE = 0x001;
    const FLAG_APPEND_NEWLINE = 0x002;

    const DEFAULT_DATE_FORMAT = "H:i:s > ";
    const DEFAULT_FLAGS = self::FLAG_APPEND_NEWLINE;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __construct($message = null, $foregroundColour = Colour\Colour::FG_DEFAULT, $backgroundColour = Colour\Colour::BG_DEFAULT, $flags = self::DEFAULT_FLAGS, $dateFormat=self::DEFAULT_DATE_FORMAT)
    {
        if (!is_null($message)) {
            $this->message($message);
        }

        $this
            ->foreground($foregroundColour)
            ->background($backgroundColour)
            ->flags($flags)
            ->dateFormat($dateFormat)
        ;
    }

    public function message($message)
    {
        $this->message = $message;
        return $this;
    }

    public function foreground($colour)
    {
        if (!is_null($colour) && !Colour\Colour::isValidColour($colour)) {
            throw new Colour\Exceptions\InvalidColourException("Invalid foreground colour '{$colour}' specified.");
        }
        $this->foreground = $colour;
        return $this;
    }

    public function background($colour)
    {
        if (!is_null($colour) && !Colour\Colour::isValidColour($colour)) {
            throw new Colour\Exceptions\InvalidColourException("Invalid background colour '{$colour}' specified.");
        }
        $this->background = $colour;
        return $this;
    }

    public function dateFormat($format)
    {
        $this->dateFormat = $format;
        return $this;
    }

    public function flags($flags)
    {
        $this->flags = $flags;
        return $this;
    }

    public function display($target=STDOUT)
    {
        return fwrite($target, (string)$this);
    }

    public function __toString()
    {
        return sprintf(
            '%s%s%s',
            (
                Flags\is_flag_set($this->flags, self::FLAG_PREPEND_DATE)
                    ? self::now($this->dateFormat)
                    : null
            ),
            Colour\Colour::colourise($this->message, $this->foreground, $this->background),
            (
                Flags\is_flag_set($this->flags, self::FLAG_APPEND_NEWLINE)
                    ? PHP_EOL
                    : null
            )
        );
    }

    private static function now($format = self::DEFAULT_DATE_FORMAT)
    {
        return (new \DateTime)->format($format);
    }
}
