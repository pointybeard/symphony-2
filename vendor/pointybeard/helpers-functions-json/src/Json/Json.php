<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Functions\Json;

use pointybeard\Helpers\Functions\Flags;
use JsonException;

if (!function_exists(__NAMESPACE__.'json_validate')) {
    /**
     * Quick way to check if a string is a valid JSON document.
     *
     * @param string $json    the string to check
     * @param mixed  $code    error code will be assigned to $code
     * @param string $message error message will be assigned to $message
     * @param int    $depth   maximum depth of traverse to
     * @param int    $options JSON_* flags to use
     *
     * @return bool true if the string is a valid JSON document
     */
    function json_validate(string $json, &$code = null, string &$message = null, int $depth = 512, ?int $options = 0): bool
    {
        $code = null;
        $message = null;
        try {
            $json = json_decode($json, false, $depth, JSON_THROW_ON_ERROR | $options);
        } catch (JsonException $ex) {
            $code = $ex->getCode();
            $message = $ex->getMessage();

            return false;
        }

        return true;
    }
}

if (!function_exists(__NAMESPACE__.'json_validate_file')) {
    /**
     * Quick way to check if a file is a valid JSON document.
     *
     * @param string $path    path to the file to check
     * @param mixed  $code    error code will be assigned to $code
     * @param string $message error message will be assigned to $message
     * @param int    $depth   maximum depth of traverse to
     * @param int    $options JSON_* flags to use
     *
     * @return bool true if the string is a valid JSON document
     */
    function json_validate_file(string $path, &$code = null, string &$message = null, int $depth = 512, ?int $options = JSON_THROW_ON_ERROR): bool
    {
        $code = null;
        $message = null;
        if (!is_readable($path)) {
            $message = "File {$path} is not readable";

            return false;
        }

        return json_validate(file_get_contents($path), $code, $message, $depth, $options);
    }
}

if (!function_exists(__NAMESPACE__.'json_decode_file')) {
    /**
     * Decode a JSON document file.
     *
     * @param string $path    path to the JSON document
     * @param bool   $assoc   true will return an associative array instead of
     *                        an instance of stdClass
     * @param int    $depth   maximum depth of traverse to
     * @param int    $options JSON_* flags to use. JSON_THROW_ON_ERROR is on
     *                        by default
     *
     * @return bool true if the string is a valid JSON document
     */
    function json_decode_file(string $path, bool $assoc = false, int $depth = 512, ?int $options = JSON_THROW_ON_ERROR)
    {
        if (!is_readable($path)) {
            $message = "The file {$path} is not readable";
            if (Flags\is_flag_set($options, JSON_THROW_ON_ERROR)) {
                throw new JsonException($message);
            } else {
                trigger_error($message, E_USER_ERROR);
            }
        }

        return json_decode(file_get_contents($path), $assoc, 512, $options);
    }
}
