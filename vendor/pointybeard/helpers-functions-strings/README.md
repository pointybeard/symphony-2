# PHP Helpers: String Functions

-   Version: v1.1.2
-   Date: June 05 2019
-   [Release notes](https://github.com/pointybeard/helpers-functions-strings/blob/master/CHANGELOG.md)
-   [GitHub repository](https://github.com/pointybeard/helpers-functions-strings)

A collection of functions for manipulating strings

## Installation

This library is installed via [Composer](http://getcomposer.org/). To install, use `composer require pointybeard/helpers-functions-strings` or add `"pointybeard/helpers-functions-strings": "~1.1.0"` to your `composer.json` file.

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

### Requirements

There are no particuar requirements for this library other than PHP 7.2 or greater.

To include all the [PHP Helpers](https://github.com/pointybeard/helpers) packages on your project, use `composer require pointybeard/helpers` or add `"pointybeard/helpers": "~1.1.0"` to your composer file.

## Usage

This library is a collection convenience function for common tasks relating to string manipulation. They are included by the vendor autoloader automatically. The functions have a namespace of `pointybeard\Helpers\Functions\Strings`

The following functions are provided:

-   `utf8_wordwrap`
-   `utf8_wordwrap_array`
-   `type_sensitive_strval`
-   `mb_str_pad`
-   `replace_placeholders_in_string`
-   `random_string`
-   `random_unique_classname`

Example usage:

```php
<?php

declare(strict_types=1);

include __DIR__.'/vendor/autoload.php';

use pointybeard\Helpers\Functions\Strings;

var_dump(Strings\utf8_wordwrap(
    'Some long string that we want to wrap at 20 characeters',
    20,
    PHP_EOL,
    true
));
// string(55) "Some long string
// that we want to wrap
// at 20 characeters"

var_dump(Strings\utf8_wordwrap_array(
    'Some long string that we want to wrap at 20 characeters',
    20,
    PHP_EOL,
    true
));
// array(3) {
//   [0] => string(16) "Some long string"
//   [1] => string(20) "that we want to wrap"
//   [2] => string(17) "at 20 characeters"
// }

var_dump(Strings\type_sensitive_strval(true));
// string(4) "true"
//
var_dump(Strings\type_sensitive_strval([1, 2, 3, 4]));
// string(5) "array"

var_dump(Strings\type_sensitive_strval(new \stdClass()));
// string(6) "object"

var_dump(Strings\mb_str_pad('Apple', 11, 'àèò', STR_PAD_LEFT, 'UTF-8'));
// string(17) "àèòàèòApple"

var_dump(Strings\mb_str_pad('Banana', 11, 'àèò', STR_PAD_RIGHT, 'UTF-8'));
// string(16) "Bananaàèòàè"

var_dump(Strings\mb_str_pad('Pear', 11, 'àèò', STR_PAD_BOTH, 'UTF-8'));
// string(18) "àèòPearàèòà"

var_dump(Strings\replace_placeholders_in_string(
    ['FIRSTNAME', 'LASTNAME', 'EMAILADDRESS'],
    ['Sarah', 'Smith', 'sarah.smith@example.com'],
    'My name is {{FIRSTNAME}} {{LASTNAME}}. Contact me at {{EMAILADDRESS}}.'
));
// string(62) "My name is Sarah Smith. Contact me at sarah.smith@example.com."

var_dump(Strings\replace_placeholders_in_string(
    ['ONE', 'TWO', 'THREE', 'FOUR'],
    ['apple', 'banana', 'orange', 'banana'],
    '[ONE], [TWO], [THREE], [NOPE]',
    true,
    '[',
    ']'
));
// string(23) "apple, banana, orange, "

var_dump(Strings\random_string(15));
// string(15) "cTAPWAi2EOCop2N"

try {
    var_dump(Strings\random_string(8, '@[^-]@i'));
} catch (Error $ex) {
    echo 'Error generating random string. returned: '.$ex->getMessage().PHP_EOL;
}
// Error generating random string. returned: minimal characters generated. filter '@[^-]@i' might be too restrictive

var_dump(Strings\random_unique_classname('test', '\\MyApp'));
// string(36) "testOIXwzi9D6bAbvy5y9QYoayS2kabbBh56"

```

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/pointybeard/helpers-functions-strings/issues),
or better yet, fork the library and submit a pull request.

## Contributing

We encourage you to contribute to this project. Please check out the [Contributing documentation](https://github.com/pointybeard/helpers-functions-strings/blob/master/CONTRIBUTING.md) for guidelines about how to get involved.

## License

"PHP Helpers: String Functions" is released under the [MIT License](http://www.opensource.org/licenses/MIT).
