# PHP Helpers: Path Functions

-   Version: v1.0.0
-   Date: May 20 2019
-   [Release notes](https://github.com/pointybeard/helpers-functions-paths/blob/master/CHANGELOG.md)
-   [GitHub repository](https://github.com/pointybeard/helpers-functions-paths)

A collection of helpful functions related to paths, directories, and files names

## Installation

This library is installed via [Composer](http://getcomposer.org/). To install, use `composer require pointybeard/helpers-functions-paths` or add `"pointybeard/helpers-functions-paths": "~1.0"` to your `composer.json` file.

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

### Requirements

There are no particuar requirements for this library other than PHP 5.6 or greater.

To include all the [PHP Helpers](https://github.com/pointybeard/helpers) packages on your project, use `composer require pointybeard/helpers` or add `"pointybeard/helpers": "~1.0"` to your composer file.

## Usage

This library is a collection of helpful functions related to paths, directories, and files names. They are included by the vendor autoloader automatically. The functions have a namespace of `pointybeard\Helpers\Functions\Paths`

The following functions are provided:

-   `is_path_absolute(string $path) : bool`
-   `get_relative_path(string $from, string $to, bool $strict = true): string`

Example usage:

```php
<?php

include __DIR__ . '/vendor/autoload.php';

use pointybeard\Helpers\Functions\Paths;

var_dump(Paths\is_path_absolute('/etc/apache2/sites-enabled/mysite.conf'));
// bool(true)

var_dump(Paths\is_path_absolute(getcwd() . '/../../potato.json'));
// bool(false)

var_dump(Paths\get_relative_path(getcwd(), getcwd() . '/some/sub/folder/path'));
// string(20) "some/sub/folder/path"

var_dump(Paths\get_relative_path('/var/www/mysite', '/var/www/someothersite'));
// string(15) "./someothersite"

try{
    Paths\get_relative_path('/var/www/mysite', '../../nonexistent', true);
} catch (\Exception $ex) {
    var_dump('ERROR! returned: ' . $ex->getMessage());
}
// string(119) "ERROR! returned: path ../../nonexistent is relative and does not exist! Make sure path exists (or set $strict to false)"

/** Same thing again, but this time with strict checking turned off **/
var_dump(Paths\get_relative_path('/var/www/mysite', '../../nonexistent', false));
// string(29) "../../../../../../nonexistent"

```

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/pointybeard/helpers-functions-paths/issues),
or better yet, fork the library and submit a pull request.

## Contributing

We encourage you to contribute to this project. Please check out the [Contributing documentation](https://github.com/pointybeard/helpers-functions-paths/blob/master/CONTRIBUTING.md) for guidelines about how to get involved.

## License

"PHP Helpers: Path Functions" is released under the [MIT License](http://www.opensource.org/licenses/MIT).
