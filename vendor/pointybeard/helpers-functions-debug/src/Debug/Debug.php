<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Functions\Debug;

use pointybeard\Helpers\Functions\Paths;
use pointybeard\Helpers\Functions\Strings;

if (!function_exists(__NAMESPACE__.'\dd')) {
    /**
     * Will var_dump() all provided values then die.
     *
     * @param mixed $args values to dump to the screen
     */
    function dd(...$args): void
    {
        foreach ($args as $a) {
            var_dump($a);
        }
        die;
    }
}

if (!function_exists(__NAMESPACE__.'\readable_debug_backtrace')) {
    /**
     * Given a trace (will use debug_backtrace() if non is supplied), this
     * function will produce a readable backtrace string for display.
     *
     * @param ?array $trace  an array of trace items (must be same format as
     *                       provided by debug_backtrace() or
     *                       \Exception::getTrace()), or null to let this
     *                       function use debug_backtrace() instead
     * @param string $format a string containing placeholders that is used
     *                       to format each line of the trace. Supported
     *                       placeholders are: PATH, FILENAME, LINE, CLASS,
     *                       TYPE, and FUNCTION.
     *
     * @return ?string a string value representing the debug trace or null
     */
    function readable_debug_backtrace(?array $trace = null, string $format = '[{{PATH}}/{{FILENAME}}:{{LINE}}] {{CLASS}}{{TYPE}}{{FUNCTION}}();'): ?string
    {
        // Nothing in the trace provided
        if (is_array($trace) && empty($trace)) {
            return null;
        } elseif (null === $trace) {
            $trace = debug_backtrace();
        }

        $base = [
            'path' => null,
            'filename' => null,
            'line' => null,
            'class' => null,
            'type' => null,
            'function' => null,
            'file' => null,
            'args' => [],
        ];

        // Set up the placeholder array (remove last 2 items, get the array keys
        // and make each one upper case)
        $placeholders = array_map('strtoupper', array_keys(array_slice($base, 0, count($base) - 2)));

        $lines = [];

        foreach ($trace as $line) {
            if (null !== $line['file']) {
                try {
                    $line['filename'] = basename($line['file']);
                    $line['path'] = dirname(Paths\get_relative_path(getcwd(), $line['file']));

                    // Something when wrong. Just use the full file path instead
                } catch (\Exception $ex) {
                    $line['path'] = $line['file'];
                }
            }

            // This will keep values from $line but order them according to
            // array keys of $base, otherwise the result from vsprintf() will
            // be wonky
            $line = array_merge($base, $line);

            // Now remove the last two items (file and args) since we don't need
            // to worry about them
            $line = array_slice($line, 0, count($line) - 2);

            // Replace the placeholder vaules in $format to produce the final
            // line
            $lines[] = Strings\replace_placeholders_in_string(
                $placeholders,
                array_values($line),
                $format,
                true
            );
        }

        return implode($lines, PHP_EOL);
    }
}
