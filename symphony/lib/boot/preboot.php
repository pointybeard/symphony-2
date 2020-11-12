<?php

/*
 This pre-boot script looks for the symphony_preboot_config path environment
 variable which is loaded and parsed. Currently it only supports
 including additional files but in future it might include other tasks.

 To use the pre-boot behaviour, follow these steps:

 1. Set `symphony_enable_preboot` to 1 either via apache envvars or .htaccess
 2. Set the path to the pre-boot JSON file with the `symphony_preboot_config`
    environment variable.
 3. Create the file `symphony_preboot_config` and specify files to include

 E.g. In the Symphony .htaccess file

 SetEnv symphony_enable_preboot 1
 SetEnv symphony_preboot_config "/path/to/the/preboot.json"

 Here is an example of the pre-boot config:

 {
     "includes": [
         "manifest/preboot/01_test.php",
         "/var/www/html/symphony/manifest/preboot/02_test.php"
     ]
 }

 Note, at this stage the Symphony core has not been initialised. There is
 no database connection and the main autoloader has not been included.
 */

declare(strict_types=1);

use Symphony\Symphony;
use pointybeard\Helpers\Functions\Json;
use pointybeard\Helpers\Exceptions\ReadableTrace\ReadableTraceException;

$isPrebootEnabled = false != getenv('symphony_enable_preboot')
    ? (bool) intval(getenv('symphony_enable_preboot'))
    : false
;

// If pre-booting is not enabled, then just return.
if (false == $isPrebootEnabled) {
    return;
}

Symphony\define_from_env('symphony_preboot_config', null, 'SYMPHONY_PREBOOT_CONFIG', Symphony\FLAG_NONE);

try {
    // Load the pre-boot config file. Expected to be valid JSON.
    $config = Json\json_decode_file(SYMPHONY_PREBOOT_CONFIG);

    // Make sure 'includes' item is set to avoid errors further down
    $config->includes = $config->includes ?? [];

    // Check each include to make sure its valid
    foreach ($config->includes as $ii => $path) {
        $path = realpath($path);

        if (false == $path) {
            throw new Symphony\Exceptions\SymphonyException('Pre-boot config contains an invalid include: %s'.$config->includes[$ii]);
        }

        $config->includes[$ii] = $path;
    }

    // Check for duplicates
    if (count($config->includes) > count(array_unique($config->includes))) {
        throw new Symphony\Exceptions\SymphonyException('Duplicate include detected in pre-boot config '.SYMPHONY_PREBOOT_CONFIG);
    }

    // All checks have passed. Include each file.
    foreach ($config->includes as $path) {
        include $path;
    }

} catch (ReadableTraceException $ex) {
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

} catch (Exception $ex) {
    // Failed to load the pre-boot config. We need to handle this here
    echo $ex->getMessage().PHP_EOL;
    exit;
}
