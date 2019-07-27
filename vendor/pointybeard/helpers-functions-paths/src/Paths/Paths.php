<?php

namespace pointybeard\Helpers\Functions\Paths;


if (!function_exists(__NAMESPACE__."\is_path_absolute")) {
    function is_path_absolute($path)
    {
        return false == strstr($path, '..');
    }
}

// Thanks to Gordon for the original function implementation that this is based
// on (https://stackoverflow.com/a/2638272)
if (!function_exists(__NAMESPACE__."\get_relative_path")) {
    function get_relative_path($from, $to, $strict = true)
    {
        if (true == $strict) {

            $fromOrig = $from;
            $toOrig = $to;

            if (!is_path_absolute($from) && null == ($from = realpath($fromOrig))) {
                throw new \Exception("path {$fromOrig} is relative and does not exist! Make sure path exists (or set \$strict to false)");
            }

            if (!is_path_absolute($to) && null == ($to = realpath($toOrig))) {
                throw new \Exception("path {$toOrig} is relative and does not exist! Make sure path exists (or set \$strict to false)");
            }
        }

        $bitsFrom = explode(DIRECTORY_SEPARATOR, $from);
        $bitsTo = explode(DIRECTORY_SEPARATOR, $to);

        $relativePathBits = $bitsTo;

        foreach ($bitsFrom as $depth => $dir) {
            if (!isset($bitsTo[$depth])) {
                // There are fewer directories in the $to path than the $from path
                // which means we're traversing up but not changing directory
                // or file name. See how many bits are left in $from path and
                // add that many '..' values
                $remaining = count($bitsFrom) - $depth;
                $relativePathBits = array_pad([], $remaining, '..');
                break;
            } elseif (0 == strcmp($dir, $bitsTo[$depth])) {
                // The current $dir is the same as the item in the $to path
                // at $depth. Shift it out of the $relativePathBits and keep going
                array_shift($relativePathBits);
            } else {
                // $dir and $bitsTo[$depth] don't match, so we're as far as we can go.
                // See how many bits are left in the from path, then add that
                // many '..' items to the start of the $relativePathBits array
                $remaining = count($bitsFrom) - $depth;

                if ($remaining <= 1) {
                    // There is exactly one item left in the $from path. This
                    // means we're only a single directory away.
                    array_unshift($relativePathBits, '.');
                } else {
                    array_unshift($relativePathBits, ...array_pad(
                        [],
                        $remaining,
                        '..'
                    ));
                    break;
                }
            }
        }

        // Join all the relative path bits to form the final relative path
        return implode(DIRECTORY_SEPARATOR, $relativePathBits);
    }
}
