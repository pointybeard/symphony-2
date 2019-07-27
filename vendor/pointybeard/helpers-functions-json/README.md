# PHP Helpers: JSON Functions

-   Version: v1.0.0
-   Date: June 08 2019
-   [Release notes](https://github.com/pointybeard/helpers-functions-json/blob/master/CHANGELOG.md)
-   [GitHub repository](https://github.com/pointybeard/helpers-functions-json)

A collection of functions for working with JSON files and strings

## Installation

This library is installed via [Composer](http://getcomposer.org/). To install, use `composer require pointybeard/helpers-functions-json` or add `"pointybeard/helpers-functions-json": "~1.0.0"` to your `composer.json` file.

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

### Requirements

This library makes use of the [PHP Helpers: Flags Functions](https://github.com/pointybeard/helpers-functions-falgs) (`pointybeard/helpers-functions-flags`). It is installed automatically via composer.

To include all the [PHP Helpers](https://github.com/pointybeard/helpers) packages on your project, use `composer require pointybeard/helpers`.

## Usage

This library is a collection convenience function for common tasks relating to working with JSON strings and documents. They are included by the vendor autoloader automatically. The functions have a namespace of `pointybeard\Helpers\Functions\Json`

The following functions are provided:

-   `json_validate`
-   `json_validate_file`
-   `json_decode_file`

Example usage:

```php
<?php

declare(strict_types=1);

include __DIR__.'/vendor/autoload.php';

use pointybeard\Helpers\Functions\Json;

/* Example 1: Check if valid string is valid JSON **/
var_dump(Json\json_validate('{"person": {"name": "Sarah Smith"}}'));
// bool(true)

/** Example 2: Check if invalid string is valid JSON **/
$isValid = Json\json_validate('{"person": {"name":}', $code, $message);
var_dump($isValid, $code, $message);
// bool(false)
// int(4)
// string(12) "Syntax error"

/** Example 3: Check if file contains valid JSON **/
$tmp = tempnam(sys_get_temp_dir(), 'JsonFunctionTest');
file_put_contents($tmp, '{"person": {"name": "Sarah Smith"}}');
var_dump(Json\json_validate_file($tmp));
// bool(true)

/* Example 4: Decode contents of valid JSON file **/
var_dump(Json\json_decode_file($tmp));
// class stdClass#2 (1) {
//   public $person =>
//   class stdClass#3 (1) {
//     public $name =>
//     string(11) "Sarah Smith"
//   }
// }

/* Example 5: Validate file containing invalid JSON **/
file_put_contents($tmp, '{"person": {"name": {"name": "Broken JSON}');
$isValid = Json\json_validate_file($tmp, $code, $message);
var_dump($isValid, $code, $message);
// bool(false)
// int(3)
// string(53) "Control character error, possibly incorrectly encoded"

/* Example 6: Attempt to decode file containing invalid JSON **/
try {
    var_dump(Json\json_decode_file($tmp));
} catch (JsonException $ex) {
    echo $ex->getMessage().PHP_EOL;
}
// Control character error, possibly incorrectly encoded

/** Example 7: Attempt to validate non-existent JSON file **/
$isValid = Json\json_validate_file('nonexistent/file.json', $code, $message);
var_dump($isValid, $code, $message);
// bool(false)
// NULL
// string(42) "File nonexistent/file.json is not readable"

/* Example 8: Attempt to decode non-existent JSON file **/
Json\json_decode_file('nonexistent/file.json');
// Fatal error: Uncaught JsonException: The file nonexistent/file.json is not readable in /path/to/helpers-functions-json/src/Json/Json.php on line 82

```

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/pointybeard/helpers-functions-json/issues),
or better yet, fork the library and submit a pull request.

## Contributing

We encourage you to contribute to this project. Please check out the [Contributing documentation](https://github.com/pointybeard/helpers-functions-json/blob/master/CONTRIBUTING.md) for guidelines about how to get involved.

## License

"PHP Helpers: JSON Functions" is released under the [MIT License](http://www.opensource.org/licenses/MIT).
