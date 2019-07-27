<?php

namespace pointybeard\Helpers\Functions\Time;

if (!function_exists(__NAMESPACE__ . '\human_readable_time')) {
    function human_readable_time($seconds, $pad=false)
    {
        if ((int)$seconds <= 0) {
            return "0 sec";
        }

        $hours = floor(namespace\seconds_to_hours($seconds));

        $remainder = $seconds - namespace\hours_to_seconds($hours);
        $minutes = floor(namespace\seconds_to_minutes($remainder));

        $seconds = $seconds - namespace\minutes_to_seconds($minutes);


        $hours = number_format($hours);
        $minutes = number_format($minutes);
        $seconds = number_format($seconds);

        if ($pad == true) {
            $hours = str_pad($hours, 3, ' ', \STR_PAD_LEFT);
            $minutes = str_pad($minutes, 2, ' ', \STR_PAD_LEFT);
            $seconds = str_pad($seconds, 2, ' ', \STR_PAD_LEFT);
        }

        return trim(
            ($hours > 0 ? sprintf("%s hr ", $hours) : '') .
            ($minutes > 0 ? sprintf("%s min ", $minutes) : '') .
            sprintf("%s sec ", $seconds)
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\seconds_to_hours')) {
    function seconds_to_hours($seconds)
    {
        return namespace\seconds_to_minutes($seconds) * (1.0 / 60.0);
    }
}

if (!function_exists(__NAMESPACE__ . '\seconds_to_minutes')) {
    function seconds_to_minutes($seconds)
    {
        return (float)$seconds * (1.0 / 60.0);
    }
}

if (!function_exists(__NAMESPACE__ . '\hours_to_seconds')) {
    function hours_to_seconds($hours)
    {
        return namespace\minutes_to_seconds($hours) * 60.0;
    }
}

if (!function_exists(__NAMESPACE__ . '\minutes_to_seconds')) {
    function minutes_to_seconds($minutes)
    {
        return (float)$minutes * 60.0;
    }
}
