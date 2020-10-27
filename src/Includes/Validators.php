<?php

// DO NOT ALTER THIS FILE!
// Instead, create /manifest/validators.php and add/edit pairs there.

$validators = [
    'number' => '/^-?(?:\d+(?:\.\d+)?|\.\d+)$/i',
    'email' => '/^\w(?:\.?[\w%+-]+)*@\w(?:[\w-]*\.)+?[a-z]{2,}$/i',
    'URI' => '/^[^\s:\/?#]+:(?:\/{2,3})?[^\s.\/?#]+(?:\.[^\s.\/?#]+)*(?:\/?[^\s?#]*\??[^\s?#]*(#[^\s#]*)?)?$/',
];

$upload = [
    'image' => '/\.(?:bmp|gif|jpe?g|png)$/i',
    'document' => '/\.(?:docx?|pdf|rtf|txt)$/i',
];

if (true == file_exists(MANIFEST.'/validators.php') && true == is_readable(MANIFEST.'/validators.php')) {
    include MANIFEST.'/validators.php';
}
