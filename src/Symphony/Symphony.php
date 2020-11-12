<?php

declare(strict_types=1);

namespace Symphony\Symphony;

use pointybeard\Helpers\Functions\Flags;

const FLAG_NONE = 0x000;
const FLAG_THROW_ON_ERROR = 0x0001;
const FLAG_ALLOW_NULL_VALUE_DEFINE = 0x0002;

/**
 * Helper method for creating defines from environment variables
 *
 * @param  string $envvar           The name of the environment variables
 * @param  string $default          Optional default value if hasn't been set
 * @param  string $customDefineName Optonally set the name of the define.
 *                                  Default is to use $name
 * @param  int $flags               Supported flags are FLAG_ALLOW_NULL_VALUE_DEFINE and FLAG_THROW_ON_ERROR
 *
 * @throws  Exceptions\SymphonyException    If $envvar doesnt exist and there is
 *                                          no $default value set, an exception
 *                                          is thrown if FLAG_THROW_ON_ERROR is
 *                                          specified.
 *
 * @see Symphony\define_safe()
 *
 * @return bool true on success, false on failure
 */
if (false == function_exists(__NAMESPACE__.'\define_from_env')) {
    function define_from_env(string $envvar, string $default = null, string $customDefineName = null, ?int $flags = FLAG_NONE): bool
    {
        $value = false !== getenv($envvar)
            ? getenv($envvar)
            : $default
        ;

        $name = $customDefineName ?? $envvar;

        if (null == $value && false == Flags\is_flag_set($flags, FLAG_ALLOW_NULL_VALUE_DEFINE)) {
            if(false == Flags\is_flag_set($flags, FLAG_THROW_ON_ERROR)) {
                return false;
            }
            throw new Exceptions\SymphonyException("Unable to define {$name}. Environment variable {$envvar} has not been set and there is no default value specified. Use flag FLAG_ALLOW_NULL_VALUE_DEFINE to allow define values to be null.");
        }

        return define_safe($name, $value, $flags);
    }
}

/**
 * Checks that a constant has not been defined before defining
 * it. If the constant is already defined, this function will do nothing
 * and return false.
 *
 * @param string $name      The name of the constant to set
 * @param mixed  $value     The value of the desired constant
 *
 * @throws  Exceptions\SymphonyException    If $name is already defined, an
 *                                          exception is thrown when
 *                                          FLAG_THROW_ON_ERROR is specified.
 *
 * @return bool true on success, false on failure
 */
if (!function_exists(__NAMESPACE__.'\define_safe')) {
    function define_safe(string $name, $value, ?int $flags = FLAG_NONE): bool
    {
        if (true == defined($name)) {
            if(Flags\is_flag_set($flags, FLAG_THROW_ON_ERROR)) {
                throw new Exceptions\SymphonyException("'{$name}' has already been defined.");
            }
            return false;
        }

        return define($name, $value);
    }
}
