<?php

namespace pointybeard\Helpers\Functions\Arrays;

if (!function_exists(__NAMESPACE__ . '\array_is_assoc')) {
    function array_is_assoc(array $input)
    {
        return array_keys($input) !== range(0, count($input) - 1);
    }
}

if (!function_exists(__NAMESPACE__ . '\array_remove_empty')) {
    function array_remove_empty(array $input, $depth=null)
    {
        if (!is_null($depth) && !is_numeric($depth)) {
            throw new Exceptions\GenericArrayFunctionsException("depth must be NULL or a positive integer value");
        }

        foreach ($input as $key => $value) {
            if (is_array($value) && (is_null($depth) || $depth > 0)) {
                $input[$key] = array_remove_empty(
                    $input[$key],
                    is_null($depth) ? null : $depth--
                );
            }

            if (empty($input[$key])) {
                unset($input[$key]);
            }
        }
        return $input;
    }
}

if (!function_exists(__NAMESPACE__ . '\array_insert_at_index')) {
    function array_insert_at_index(&$array, $index, ...$additions) {

        if (!is_numeric($index)) {
            throw new Exceptions\GenericArrayFunctionsException("index must be an integer");
        }

        foreach($additions as $a) {
            array_splice($array, $index, 0, $a);
            // Advance the index so each additon is inserted after the previous one
            $index++;
        }
    }
}
