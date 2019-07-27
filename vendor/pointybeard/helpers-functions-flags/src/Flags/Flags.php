<?php

namespace pointybeard\Helpers\Functions\Flags;

if (!function_exists(__NAMESPACE__ . '\is_flag_set')) {
    /**
     * Convienence method for determining if a bitwise flag constant is set
     * in a collection of flags
     * @param  int  $flags set of flags to examine
     * @param  int  $flag  the flag to look for
     * @return boolean        returns true if $flag is set in $flags
     */
    function is_flag_set($flags, $flag)
    {
        // Flags support bitwise operators so it's easy to see
        // if one has been set.
        return ($flags & $flag) == $flag;
    }
}
