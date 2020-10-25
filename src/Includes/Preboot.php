<?php

//declare(strict_types=1);

use pointybeard\Helpers\Functions\Json;
use Symphony\Symphony;
use Symphony\Symphony\Exceptions;

$isPrebootEnabled = false != getenv('symphony_enable_preboot')
    ? (bool) intval(getenv('symphony_enable_preboot'))
    : false
;

// If pre-booting is not enabled, then just return.
if (false == $isPrebootEnabled) {
    return;
}

/*
 * Helper method for creating defines from environment variables
 * @param  string $name             The name of the environment variables
 * @param  string $default          Optional default value if hasn't been set
 * @param  string $customDefineName Optonally set the name of the define.
 *                                  Default is to use $name
 * @throws  Exception               If $name is not a valid environment variable
 *                                  and there is no $default value set, an
 *                                  exception is thrown
 */
if (!function_exists('defineFromEnv')) {
    function defineFromEnv(string $name, string $default = null, string $customDefineName = null): void
    {
        $value = false !== getenv($name)
            ? getenv($name)
            : $default
        ;

        if (null == $value) {
            throw new Exceptions\SymphonyException("Environment variable {$name} has not been set and there is no default value. Set {$name} with either `export {$name}=xxx` or a line in Apache envvars.");
        }

        define(
            (null != $customDefineName ? $customDefineName : $name),
            $value
        );
    }
}

try {
    defineFromEnv('symphony_preboot_config', null, 'SYMPHONY_PREBOOT_CONFIG');
} catch (Exception $ex) {
    // There was no environment variable set, so we dont need to do anything
    // else.
    return;
}

try {
    // Load the pre-boot config file. Expected to be valid JSON.
    $config = Json\json_decode_file(SYMPHONY_PREBOOT_CONFIG);

    // Make sure 'includes' item is set to avoid errors further down
    $config->includes = $config->includes ?? [];

    // Check each include to make sure its valid
    foreach ($config->includes as $ii => $path) {
        $path = realpath($path);

        if (false == $path) {
            throw new Exceptions\SymphonyException('Pre-boot config contains an invalid include: %s'.$config->includes[$ii]);
        }

        $config->includes[$ii] = $path;
    }

    // Check for duplicates
    if (count($config->includes) > count(array_unique($config->includes))) {
        throw new Exceptions\SymphonyException('Duplicate include detected in pre-boot config '.SYMPHONY_PREBOOT_CONFIG);
    }

    // All checks have passed. Include each file.
    foreach ($config->includes as $path) {
        include $path;
    }
} catch (\pointybeard\Helpers\Exceptions\ReadableTrace $ex) {
    // Template for displaying the exception to the screen
    $message = <<<OUTPUT
[%s]
An error occurred around line %d of %s. The following was returned:

%s

Backtrace
==========
%s

OUTPUT;

    printf(
        $message,
        (new \ReflectionClass($ex))->getName(),
        $ex->getLine(),
        $ex->getFile(),
        $ex->getMessage(),
        $ex->getReadableTrace()
    );
    exit;
} catch (Exception $ex) {
    // Failed to load the pre-boot config. We need to handle this here
    echo $ex->getMessage().PHP_EOL;
    exit;
}

// All done. Resume rest of the Symphony boot process
