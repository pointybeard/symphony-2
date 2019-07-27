<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Functions\Cli;

use pointybeard\Helpers\Cli\Input;
use pointybeard\Helpers\Cli\Colour;
use pointybeard\Helpers\Functions\Flags;
use pointybeard\Helpers\Functions\Strings;
use pointybeard\Helpers\Functions\Arrays;
use pointybeard\Helpers\Functions\Debug;

/*
 * Checks if bash can be invoked.
 *
 * Credit to Troels Knak-Nielsen for inspiring this code.
 * (http://www.sitepoint.com/interactive-cli-password-prompt-in-php/)
 *
 * @return bool true if bash can be invoked
 */
if (!function_exists(__NAMESPACE__.'can_invoke_bash')) {
    function can_invoke_bash(): bool
    {
        return 0 === strcmp(trim(shell_exec("/usr/bin/env bash -c 'echo OK'")), 'OK');
    }
}

/*
 * Checks if script is running as root user
 *
 * @return bool true if user is root
 */
if (!function_exists(__NAMESPACE__.'is_su')) {
    function is_su(): bool
    {
        $userinfo = posix_getpwuid(posix_geteuid());

        return (bool) (0 == $userinfo['uid'] || 'root' == $userinfo['name']);
    }
}

/*
 * Uses tput to find out the size of the window (columns and lines)
 * @return array an array containing exactly 2 items: 'cols' and 'lines'
 */
if (!function_exists(__NAMESPACE__.'get_window_size')) {
    function get_window_size(): array
    {
        return [
            'cols' => exec('tput cols'),
            'lines' => exec('tput lines'),
        ];
    }
}

if (!function_exists(__NAMESPACE__.'usage')) {
    function usage(string $name, Input\InputCollection $collection): string
    {
        $arguments = [];
        foreach ($collection->getItemsByType('Argument') as $a) {
            $arguments[] = strtoupper(
                // Wrap with square brackets if it's not required
                Flags\is_flag_set(Input\AbstractInputType::FLAG_OPTIONAL, $a->flags()) ||
                !Flags\is_flag_set(Input\AbstractInputType::FLAG_REQUIRED, $a->flags())
                    ? "[{$a->name()}]"
                    : $a->name()
            );
        }
        $arguments = trim(implode($arguments, ' '));

        return sprintf(
            'Usage: %s [OPTIONS]... %s%s',
            $name,
            $arguments,
            strlen($arguments) > 0 ? '...' : ''
        );
    }
}

if (!function_exists(__NAMESPACE__.'manpage')) {
    function manpage(string $name, string $version, string $description, Input\InputCollection $collection, $foregroundColour = Colour\Colour::FG_DEFAULT, $headingColour = Colour\Colour::FG_WHITE, array $additionalSections = []): string
    {
        // Convienence function for wrapping a heading with colour
        $heading = function (string $input) use ($headingColour) {
            return Colour\Colour::colourise($input, $headingColour);
        };

        // Convienence function for wrapping input in a specified colour
        $colourise = function (string $input) use ($foregroundColour) {
            return Colour\Colour::colourise($input, $foregroundColour);
        };

        $sections = [
            $colourise(sprintf(
                "%s %s, %s\r\n%s\r\n",
                $name,
                $version,
                $description,
                usage($name, $collection)
            )),
        ];

        $arguments = [];
        $options = [];

        foreach ($collection->getItemsByType('Argument') as $a) {
            $arguments[] = (string) $a;
        }

        foreach ($collection->getItemsExcludeByType('Argument') as $o) {
            $options[] = (string) $o;
        }

        // Add the arguments, if there are any.
        if (false === empty($arguments)) {
            $sections[] = $heading('Arguments:');
            $sections[] = $colourise(implode($arguments, PHP_EOL)).PHP_EOL;
        }

        // Add the options, if there are any.
        if (false === empty($options)) {
            $sections[] = $heading('Options:');
            $sections[] = $colourise(implode($options, PHP_EOL)).PHP_EOL;
        }

        // Iterate over all additional items and add them as new sections
        foreach ($additionalSections as $name => $contents) {
            $sections[] = $heading("{$name}:");
            $sections[] = $colourise($contents).PHP_EOL;
        }

        return implode($sections, PHP_EOL);
    }
}

if (!function_exists(__NAMESPACE__."\display_error_and_exit")) {
    function display_error_and_exit($message, $heading = 'Error', $background = Colour\Colour::BG_RED, ?array $trace = null): void
    {
        $padCharacter = ' ';
        $paddingBufferSize = 0.15; // 15%
        $minimumWindowWidth = 40;
        $edgePaddingLength = 5;
        $edgePadding = str_repeat($padCharacter, $edgePaddingLength);

        // Convenience function for adding the background to a line.
        $add_background = function (string $string, bool $bold = false) use ($padCharacter, $edgePadding, $background): string {
            $string = $edgePadding.$string.$edgePadding;

            return Colour\Colour::colourise(
                $string,
                (
                    true == $bold
                        ? Colour\Colour::FG_WHITE
                        : Colour\Colour::FG_DEFAULT
                ),
                $background
            );
        };

        // Get the window dimensions but restrict width to minimum
        // of $minimumWindowWidth
        $window = get_window_size();
        $window['cols'] = max($minimumWindowWidth, $window['cols']);

        // This shrinks the total line length (derived by the window width) by
        // $paddingBufferSize
        $paddingBuffer = (int) ceil($window['cols'] * $paddingBufferSize);

        $lineLength = $window['cols'] - (2 * $edgePaddingLength) - $paddingBuffer;

        $emptyLine = $add_background(str_repeat($padCharacter, $lineLength), true);
        $heading = Strings\mb_str_pad(trim($heading), $lineLength, $padCharacter, \STR_PAD_RIGHT);

        $message = Strings\utf8_wordwrap_array($message, $lineLength, PHP_EOL, true);

        // Remove surrounding whitespace
        $message = array_map('trim', $message);

        // Remove empty elements from the array
        $message = Arrays\array_remove_empty($message);

        // Reset array indicies
        $message = array_values($message);

        // Wrap everything in red
        for ($ii = 0; $ii < count($message); ++$ii) {
            $message[$ii] = $add_background(Strings\mb_str_pad(
                $message[$ii],
                mb_strlen($heading),
                $padCharacter,
                \STR_PAD_RIGHT
            ));
        }

        // Add an empty red line at the end
        array_push($message, $emptyLine);

        // Print the error message, starting with an empty red line
        printf(
            "\r\n%s\r\n%s\r\n%s\r\n%s",
            $emptyLine,
            $add_background($heading, true),
            implode($message, PHP_EOL),
            !empty($trace) && count($trace) > 0
                ? PHP_EOL.sprintf("Trace\r\n==========\r\n%s\r\n", Debug\readable_debug_backtrace($trace))
                : ''
        );

        exit(1);
    }
}
