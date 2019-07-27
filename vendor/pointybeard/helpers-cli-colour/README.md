# PHP Helpers: Command-line Colour

-   Version: v1.0.2
-   Date: May 05 2019
-   [Release notes](https://github.com/pointybeard/helpers-cli-colour/blob/master/CHANGELOG.md)
-   [GitHub repository](https://github.com/pointybeard/helpers-cli-colour)

Provides colour constants and a simple way to colourise strings for use on the command-line.

## Installation

This library is installed via [Composer](http://getcomposer.org/). To install, use `composer require pointybeard/helpers-cli-colour` or add `"pointybeard/helpers-cli-colour": "~1.0"` to your `composer.json` file.

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

### Requirements

There are no particuar requirements for this library other than PHP 5.6 or greater.

To include all the [PHP Helpers](https://github.com/pointybeard/helpers) packages on your project, use `composer require pointybeard/helpers` or add `"pointybeard/helpers": "~1.0"` to your composer file.

## Usage

Here is an example of how to use the Colour class:

```php
<?php

include __DIR__ . "/vendor/autoload.php";

use pointybeard\Helpers\Cli\Colour;

// Access colour constants
$foreground = Colour\Colour::FG_RED;
$background = Colour\Colour::BG_LIGHT_YELLOW;

try{

    // Add colour to a string
    print Colour\Colour::colourise(
        "This is my colouful string!!",
        $foreground,
        $background
    );

    print PHP_EOL;

    // Throws a InvalidColourException exception if the colour is invalid
    Colour\Colour::colourise(
        "Some string",
        "banana",
        $background
    );

} catch (Colour\Exceptions\InvalidColourException $ex) {
    print "ERROR: " . $ex->getMessage() . PHP_EOL;
}

```

### Colours

The following colour constants exist:

#### Foreground
FG_DEFAULT, FG_BLACK, FG_RED, FG_GREEN, FG_BROWN, FG_BLUE, FG_PURPLE, FG_CYAN, FG_WHITE, FG_DARK_GRAY, FG_LIGHT_RED, FG_LIGHT_GREEN, FG_YELLOW, FG_LIGHT_BLUE, FG_LIGHT_PURPLE, FG_LIGHT_CYAN, FG_LIGHT_GRAY

#### Background
BG_BLACK, BG_RED, BG_GREEN, BG_YELLOW, BG_BLUE, BG_MAGENTA, BG_CYAN, BG_DEFAULT, BG_WHITE, BG_LIGHT_GRAY, BG_LIGHT_RED, BG_LIGHT_GREEN, BG_LIGHT_YELLOW, BG_LIGHT_BLUE, BG_LIGHT_MAGENTA, BG_LIGHT_CYAN, BG_DARK_GRAY

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/pointybeard/helpers-cli-colour/issues),
or better yet, fork the library and submit a pull request.

## Contributing

We encourage you to contribute to this project. Please check out the [Contributing documentation](https://github.com/pointybeard/helpers-cli-colour/blob/master/CONTRIBUTING.md) for guidelines about how to get involved.

## License

"PHP Helpers: Command-line Colour" is released under the [MIT License](http://www.opensource.org/licenses/MIT).
