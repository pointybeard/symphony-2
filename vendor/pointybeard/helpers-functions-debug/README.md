# PHP Helpers: Debug Functions

-   Version: v1.0.1
-   Date: May 26 2019
-   [Release notes](https://github.com/pointybeard/helpers-functions-debug/blob/master/CHANGELOG.md)
-   [GitHub repository](https://github.com/pointybeard/helpers-functions-debug)

A collection of helpful functions to assist with debugging

## Installation

This library is installed via [Composer](http://getcomposer.org/). To install, use `composer require pointybeard/helpers-functions-debug` or add `"pointybeard/helpers-functions-debug": "~1.1"` to your `composer.json` file.

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

### Requirements

This library makes use of the [PHP Helpers: Path Functions](https://github.com/pointybeard/helpers-functions-paths) (`pointybeard/helpers-functions-paths`) and [PHP Helpers: String Functions](https://github.com/pointybeard/helpers-functions-strings) (`pointybeard/helpers-functions-strings`). They are installed automatically via composer.

To include all the [PHP Helpers](https://github.com/pointybeard/helpers) packages on your project, use `composer require pointybeard/helpers` or add `"pointybeard/helpers": "~1.1"` to your composer file.

## Usage

This library is a collection of helpful functions to assist with debugging. They are included by the vendor autoloader automatically. The functions have a namespace of `pointybeard\Helpers\Functions\Debug`

The following functions are provided:

-   `dd()`
-   `readable_debug_backtrace()`

Example usage:

```php
<?php

declare(strict_types=1);

include __DIR__.'/vendor/autoload.php';

use pointybeard\Helpers\Functions\Debug;

echo Debug\readable_debug_backtrace().PHP_EOL;
// [./test.php:7] pointybeard\Helpers\Functions\Debug\readable_debug_backtrace();
//
echo Debug\readable_debug_backtrace(null, '{{FUNCTION}}() in {{FILENAME}} on line {{LINE}}').PHP_EOL;
// pointybeard\Helpers\Functions\Debug\readable_debug_backtrace() in test.php on line 10

$sampleTrace = [
    [
        'file' => 'Console/AbstractCommand.php',
        'line' => '38',
        'function' => 'init',
        'class' => 'AbstractCommand',
        'type' => '->',
    ],
    [
        'file' => 'console/commands/Symphony.php',
        'line' => '18',
        'function' => '__construct',
        'class' => 'AbstractCommand',
        'type' => '->',
    ],
    [
        'file' => 'vendor/pointybeard/helpers-foundation-factory/src/Factory/AbstractFactory.php',
        'line' => '57',
        'function' => '__construct',
        'class' => "Commands\Console\Symphony",
        'type' => '->',
    ],
    [
        'file' => 'Console/CommandFactory.php',
        'line' => '47',
        'function' => 'instanciate',
        'class' => "Factory\AbstractFactory",
        'type' => '::',
    ],
    [
        'file' => 'console/bin/symphony',
        'line' => '54',
        'function' => 'build',
        'class' => 'CommandFactory',
        'type' => '::',
    ],
];

echo Debug\readable_debug_backtrace($sampleTrace, '{{CLASS}}{{TYPE}}{{FUNCTION}}() in {{FILENAME}} on line {{LINE}}').PHP_EOL;
// AbstractCommand->init() in AbstractCommand.php on line 38
// AbstractCommand->__construct() in Symphony.php on line 18
// Commands\Console\Symphony->__construct() in AbstractFactory.php on line 57
// Factory\AbstractFactory::instanciate() in CommandFactory.php on line 47
// CommandFactory::build() in symphony on line 54

Debug\dd(
    'apple',
    1,
    false,
    []
);
// string(5) "apple"
// int(1)
// bool(false)
// array(0) {}

```

### Placeholders

The format of each trace line produced by Debug\readable_debug_backtrace() can be modified by setting the `format` argument. The default format is `[{{PATH}}/{{FILENAME}}:{{LINE}}] {{CLASS}}{{TYPE}}{{FUNCTION}}();` which looks something like `[../path/to/test.php:24] foo->__construct();`.

Placeholders available are:

-   PATH
-   FILENAME
-   LINE
-   CLASS
-   TYPE
-   FUNCTION

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/pointybeard/helpers-functions-debug/issues),
or better yet, fork the library and submit a pull request.

## Contributing

We encourage you to contribute to this project. Please check out the [Contributing documentation](https://github.com/pointybeard/helpers-functions-debug/blob/master/CONTRIBUTING.md) for guidelines about how to get involved.

## License

"PHP Helpers: Debug Functions" is released under the [MIT License](http://www.opensource.org/licenses/MIT).
