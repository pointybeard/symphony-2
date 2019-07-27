# PHP Helpers: Command-line Functions

-   Version: v1.1.8
-   Date: June 01 2019
-   [Release notes](https://github.com/pointybeard/helpers-functions-cli/blob/master/CHANGELOG.md)
-   [GitHub repository](https://github.com/pointybeard/helpers-functions-cli)

A collection of functions relating to the command-line

## Installation

This library is installed via [Composer](http://getcomposer.org/). To install, use `composer require pointybeard/helpers-functions-cli` or add `"pointybeard/helpers-functions-cli": "~1.1"` to your `composer.json` file.

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

### Requirements

This library makes use of the [PHP Helpers: Command-line Input and Input Type Handlers](https://github.com/pointybeard/helpers-cli-input), [PHP Helpers: Flag Functions](https://github.com/pointybeard/helpers-functions-flags) (`pointybeard/helpers-functions-flags`), [PHP Helpers: Command-line Colour](https://github.com/pointybeard/helpers-cli-colours) (`pointybeard/helpers-cli-colours`) and [PHP Helpers: String Functions](https://github.com/pointybeard/helpers-functions-strings) packages. They are installed automatically via composer.

To include all the [PHP Helpers](https://github.com/pointybeard/helpers) packages on your project, use `composer require pointybeard/helpers` or add `"pointybeard/helpers": "~1"` to your composer file.

## Usage

This library is a collection convenience function for command-line tasks. They are included by the vendor autoloader automatically. The functions have a namespace of `pointybeard\Helpers\Functions\Cli`

The following functions are provided:

-   `can_invoke_bash()`
-   `is_su()`
-   `usage()`
-   `manpage()`
-   `get_window_size()`
-   `display_error_and_exit()`

Example usage:

```php
<?php

declare(strict_types=1);
include __DIR__.'/vendor/autoload.php';

use pointybeard\Helpers\Cli\Input;
use pointybeard\Helpers\Cli\Colour\Colour;
use pointybeard\Helpers\Functions\Cli;

var_dump(Cli\can_invoke_bash());
// bool(true)

var_dump(Cli\is_su());
// bool(false)

var_dump(Cli\get_window_size());
// array(2) {
//   'cols' => string(3) "103"
//   'lines' => string(2) "68"
// }

echo Cli\manpage(
    'test',
    '1.0.2',
    'A simple test command with a really long description. This is an intentionally very long argument description so we can check that word wrapping is working correctly. It should wrap to the window',
    (new Input\InputCollection())
        ->add(
            Input\InputTypeFactory::build('Argument')
                ->name('action')
                ->flags(Input\AbstractInputType::FLAG_REQUIRED)
                ->description('The name of the action to perform. This is an intentionally very long argument description so we can check that word wrapping is working correctly')
        )
        ->add(
            Input\InputTypeFactory::build('IncrementingFlag')
                ->name('v')
                ->flags(Input\AbstractInputType::FLAG_OPTIONAL | Input\AbstractInputType::FLAG_TYPE_INCREMENTING)
                ->description('verbosity level. -v (errors only), -vv (warnings and errors), -vvv (everything).')
                ->validator(new Input\Validator(
                    function (Input\AbstractInputType $input, Input\AbstractInputHandler $context) {
                        // Make sure verbosity level never goes above 3
                        return min(3, (int) $context->find('v'));
                    }
                ))
        )
        ->add(
            Input\InputTypeFactory::build('Option')
                ->name('P')
                ->flags(Input\AbstractInputType::FLAG_OPTIONAL | Input\AbstractInputType::FLAG_VALUE_OPTIONAL)
                ->description('Port to use for all connections.')
                ->default('3306')
        )
        ->add(
            Input\InputTypeFactory::build('LongOption')
                ->name('data')
                ->short('d')
                ->flags(Input\AbstractInputType::FLAG_OPTIONAL | Input\AbstractInputType::FLAG_VALUE_REQUIRED)
                ->description('Path to the input JSON data.')
        ),
    Colour::FG_GREEN,
    Colour::FG_WHITE,
    [
        'Examples' => 'php -f test.php -- import -vvv -d test.json',
    ]
).PHP_EOL;

// test 1.0.2, A simple test command with a really long description. This is an intentionally very long argument description so we can check that word wrapping is working correctly. It should wrap to the window
// Usage: test [OPTIONS]... ACTION...
//
// Arguments:
// ACTION              The name of the action to perform. This is an intentionally very
//                     long argument description so we can check that word wrapping is
//                     working correctly
//
// Options:
// -v                            verbosity level. -v (errors only), -vv (warnings and errors),
//                               -vvv (everything).
// -P                            Port to use for all connections.
// -d, --data=VALUE              Path to the input JSON data.
//
// Examples:
// php -f test.php -- import -vvv -d test.json

Cli\display_error_and_exit('Looks like something went wrong!', 'Fatal Error');
// Fatal Error
// Looks like something went wrong!

```

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/pointybeard/helpers-functions-cli/issues),
or better yet, fork the library and submit a pull request.

## Contributing

We encourage you to contribute to this project. Please check out the [Contributing documentation](https://github.com/pointybeard/helpers-functions-cli/blob/master/CONTRIBUTING.md) for guidelines about how to get involved.

## License

"PHP Helpers: Command-line Functions" is released under the [MIT License](http://www.opensource.org/licenses/MIT).
