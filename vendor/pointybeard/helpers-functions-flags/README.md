# PHP Helpers: Flag Functions

-   Version: v1.0.0
-   Date: May 08 2019
-   [Release notes](https://github.com/pointybeard/helpers-functions-flags/blob/master/CHANGELOG.md)
-   [GitHub repository](https://github.com/pointybeard/helpers-functions-flags)

A collection of functions for handling bitwise flags

## Installation

This library is installed via [Composer](http://getcomposer.org/). To install, use `composer require pointybeard/helpers-functions-flags` or add `"pointybeard/helpers-functions-flags": "~1.0"` to your `composer.json` file.

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

### Requirements

There are no particuar requirements for this library other than PHP 5.6 or greater.

To include all the [PHP Helpers](https://github.com/pointybeard/helpers) packages on your project, use `composer require pointybeard/helpers` or add `"pointybeard/helpers": "~1.0"` to your composer file.

## Usage

This library is a collection convenience function for common tasks relating to bitwise flags. They are included by the vendor autoloader automatically. The functions have a namespace of `pointybeard\Helpers\Functions\Flags`

The following functions are provided:

-   `is_flag_set(int $flags, int $flag)`

Example usage:

```php
<?php

include __DIR__ . '/vendor/autoload.php';

use pointybeard\Helpers\Functions\Flags;

const FLAG_A = 0x0001;
const FLAG_B = 0x0002;
const FLAG_C = 0x0004;

$flags = FLAG_B | FLAG_C;

var_dump(Flags\is_flag_set($flags, FLAG_C));
// bool(true)

var_dump(Flags\is_flag_set($flags, FLAG_A));
// bool(false)
```

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/pointybeard/helpers-functions-flags/issues),
or better yet, fork the library and submit a pull request.

## Contributing

We encourage you to contribute to this project. Please check out the [Contributing documentation](https://github.com/pointybeard/helpers-functions-flags/blob/master/CONTRIBUTING.md) for guidelines about how to get involved.

## License

"PHP Helpers: Flag Functions" is released under the [MIT License](http://www.opensource.org/licenses/MIT).
