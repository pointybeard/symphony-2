# PHP Helpers: Command-line Input and Input Type Handlers

-   Version: v1.2.1
-   Date: June 04 2019
-   [Release notes](https://github.com/pointybeard/helpers-cli-input/blob/master/CHANGELOG.md)
-   [GitHub repository](https://github.com/pointybeard/helpers-cli-input)

Collection of classes for handling argv (and other) input when calling command-line scripts. Helps with parsing, collecting and validating arguments, options, and flags.

## Installation

This library is installed via [Composer](http://getcomposer.org/). To install, use `composer require pointybeard/helpers-cli-input` or add `"pointybeard/helpers-cli-input": "~1.2.0"` to your `composer.json` file.

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

### Requirements

This library makes use of the [PHP Helpers: Flag Functions](https://github.com/pointybeard/helpers-functions-flags) (`pointybeard/helpers-functions-flags`) and [PHP Helpers: Factory Foundation Classes](https://github.com/pointybeard/helpers-foundation-factory) packages. They are installed automatically via composer.

To include all the [PHP Helpers](https://github.com/pointybeard/helpers) packages on your project, use `composer require pointybeard/helpers` or add `"pointybeard/helpers": "~1.1"` to your composer file.

## Usage

Include this library in your PHP files with `use pointybeard\Helpers\Cli`. See example code in `example/example.php`. The example code can be run with the following command:

    php -f example/example.php -- -vvv -d example/example.json import

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/pointybeard/helpers-cli-input/issues),
or better yet, fork the library and submit a pull request.

## Contributing

We encourage you to contribute to this project. Please check out the [Contributing documentation](https://github.com/pointybeard/helpers-cli-input/blob/master/CONTRIBUTING.md) for guidelines about how to get involved.

## License

"PHP Helpers: Command-line Input and Input Type Handlers" is released under the [MIT License](http://www.opensource.org/licenses/MIT).

## Credits

*   Some inspiration taken from the [Symfony Console Component](https://github.com/symfony/console) (although no code has been used).
