<?php

namespace pointybeard\Helpers\Cli\Colour;

class Colour
{
    const FG_DEFAULT = '0;39';
    const FG_BLACK = '0;30';
    const FG_RED = '0;31';
    const FG_GREEN = '0;32';
    const FG_BROWN = '0;33';
    const FG_BLUE = '0;34';
    const FG_PURPLE = '0;35';
    const FG_CYAN = '0;36';
    const FG_WHITE = '1;37';
    const FG_DARK_GRAY = '1;30';
    const FG_LIGHT_RED = '1;31';
    const FG_LIGHT_GREEN = '1;32';
    const FG_YELLOW = '1;33';
    const FG_LIGHT_BLUE = '1;34';
    const FG_LIGHT_PURPLE = '1;35';
    const FG_LIGHT_CYAN = '1;36';
    const FG_LIGHT_GRAY = '0;37';

    const BG_BLACK = '40';
    const BG_RED = '41';
    const BG_GREEN = '42';
    const BG_YELLOW = '43';
    const BG_BLUE = '44';
    const BG_MAGENTA = '45';
    const BG_CYAN = '46';
    const BG_DEFAULT = '49';
    const BG_WHITE = '107';
    const BG_LIGHT_GRAY = '47';
    const BG_LIGHT_RED = '101';
    const BG_LIGHT_GREEN = '102';
    const BG_LIGHT_YELLOW = '103';
    const BG_LIGHT_BLUE = '104';
    const BG_LIGHT_MAGENTA = '105';
    const BG_LIGHT_CYAN = '106';
    const BG_DARK_GRAY = '100';

    // Convenience array used by
    // isValidColour() and isValidForegroundColour()
    private static $foregroundColours = [
        self::FG_DEFAULT,
        self::FG_BLACK,
        self::FG_RED,
        self::FG_GREEN,
        self::FG_BROWN,
        self::FG_BLUE,
        self::FG_PURPLE,
        self::FG_CYAN,
        self::FG_WHITE,
        self::FG_DARK_GRAY,
        self::FG_LIGHT_RED,
        self::FG_LIGHT_GREEN,
        self::FG_YELLOW,
        self::FG_LIGHT_BLUE,
        self::FG_LIGHT_PURPLE,
        self::FG_LIGHT_CYAN,
        self::FG_LIGHT_GRAY,
    ];

    // Convenience array used by
    // isValidColour() and isValidBackgroundColour()
    private static $backgroundColours = [
        self::BG_BLACK,
        self::BG_RED,
        self::BG_GREEN,
        self::BG_YELLOW,
        self::BG_BLUE,
        self::BG_MAGENTA,
        self::BG_CYAN,
        self::BG_DEFAULT,
        self::BG_WHITE,
        self::BG_LIGHT_GRAY,
        self::BG_LIGHT_RED,
        self::BG_LIGHT_GREEN,
        self::BG_LIGHT_YELLOW,
        self::BG_LIGHT_BLUE,
        self::BG_LIGHT_MAGENTA,
        self::BG_LIGHT_CYAN,
        self::BG_DARK_GRAY,
    ];

    const COLOURISE_OPEN = "\e[%sm";
    const COLOURISE_CLOSE = "\033[0m";

    private static function getColourisePattern()
    {
        return sprintf(
            '%s%1$s%%s%s',
            self::COLOURISE_OPEN,
            self::COLOURISE_CLOSE
        );
    }

    public static function colourise($string, $foreground, $background = self::BG_DEFAULT)
    {
        if (!self::isValidForegroundColour($foreground)) {
            throw new Exceptions\InvalidColourException("Invalid foreground colour '{$foreground}' specified.");
        }

        if (!self::isValidBackgroundColour($background)) {
            throw new Exceptions\InvalidColourException("Invalid background colour '{$background}' specified.");
        }

        return sprintf(self::getColourisePattern(), $foreground, $background, $string);
    }

    public static function isValidColour($colour)
    {
        return
            self::isValidForegroundColour($colour)
            ||
            self::isValidBackgroundColour($colour)
        ;
    }

    public static function isValidBackgroundColour($colour)
    {
        return in_array($colour, self::$backgroundColours);
    }

    public static function isValidForegroundColour($colour)
    {
        return in_array($colour, self::$foregroundColours);
    }
}
