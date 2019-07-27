# PHP Helpers: Time Functions

-   Version: v1.0.1
-   Date: May 08 2019
-   [Release notes](https://github.com/pointybeard/helpers-functions-time/blob/master/CHANGELOG.md)
-   [GitHub repository](https://github.com/pointybeard/helpers-functions-time)

A collection of functions used to manipulate time values

## Installation

This library is installed via [Composer](http://getcomposer.org/). To install, use `composer require pointybeard/helpers-functions-time` or add `"pointybeard/helpers-functions-time": "~1.0"` to your `composer.json` file.

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

### Requirements

There are no particuar requirements for this library other than PHP 5.6 or greater.

To include all the [PHP Helpers](https://github.com/pointybeard/helpers) packages on your project, use `composer require pointybeard/helpers` or add `"pointybeard/helpers": "~1.0"` to your composer file.

## Usage

This library is a collection convenience function for common tasks relating to time. They are included by the vendor autoloader automatically. The functions have a namespace of `pointybeard\Helpers\Functions\Time`

The following functions are provided:

-   `human_readable_time(int $seconds, $pad=false)`
-   `seconds_to_hours(int $seconds)`
-   `seconds_to_minutes(int $seconds)`
-   `hours_to_seconds(int $hours)`
-   `minutes_to_seconds(int $minutes)`

Example usage:

```php
<?php

include __DIR__ . '/vendor/autoload.php';

use pointybeard\Helpers\Functions\Time;

print Time\human_readable_time(1801);
// Result: 30 min 1 sec

```

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/pointybeard/helpers-functions-time/issues),
or better yet, fork the library and submit a pull request.

## Contributing

We encourage you to contribute to this project. Please check out the [Contributing documentation](https://github.com/pointybeard/helpers-functions-time/blob/master/CONTRIBUTING.md) for guidelines about how to get involved.

## License

"PHP Helpers: Time Functions" is released under the [MIT License](http://www.opensource.org/licenses/MIT).
