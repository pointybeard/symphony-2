# PHP Helpers: Command-line Message

-   Version: v1.0.0
-   Date: May 16 2019
-   [Release notes](https://github.com/pointybeard/helpers-cli-message/blob/master/CHANGELOG.md)
-   [GitHub repository](https://github.com/pointybeard/helpers-cli-message)

A class to make it easier to print messages to the command-line

## Installation

This library is installed via [Composer](http://getcomposer.org/). To install, use `composer require pointybeard/helpers-cli-message` or add `"pointybeard/helpers-cli-message": "~1.0"` to your `composer.json` file.

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

### Requirements

This library makes use of the [PHP Helpers: Flag Functions](https://github.com/pointybeard/helpers-functions-flags) (`pointybeard/helpers-functions-flags`) and [PHP Helpers: Command-line Colour](https://github.com/pointybeard/helpers-cli-colour) (`pointybeard/helpers-cli-color`) packages. They are installed automatically via composer.

To include all the [PHP Helpers](https://github.com/pointybeard/helpers) packages on your project, use `composer require pointybeard/helpers` or add `"pointybeard/helpers": "~1.0"` to your composer file.

## Usage

Include this library in your PHP files with `use pointybeard\Helpers\Cli\Message` and instanciate the `Message\Message` class like so:

```php
<?php

include __DIR__ . "/vendor/autoload.php";

use pointybeard\Helpers\Cli\Message\Message;
use pointybeard\Helpers\Cli\Colour;

(new Message("This is a message"))->display();
// This is a message

(new Message("This is a message with the date"))
    ->flags(Message::FLAG_PREPEND_DATE | MESSAGE::FLAG_APPEND_NEWLINE)
    ->display()
;
// 06:34:52 > This is a message with the date

(new Message("Message with custom date format"))
    ->dateFormat("M, d D Y~ ")
    ->flags(Message::FLAG_PREPEND_DATE | MESSAGE::FLAG_APPEND_NEWLINE)
    ->display()
;
// May, 16 Thu 2019~ Message with custom date format

(new Message("Error: This is a fancy looking error message"))
    ->foreground(Colour\Colour::FG_WHITE)
    ->background(Colour\Colour::BG_RED)
    ->flags(MESSAGE::FLAG_APPEND_NEWLINE)
    ->display()
;
// Error: This is a fancy looking error message

(new Message("All arguments in the constructor", Colour\Colour::FG_GREEN, Colour\Colour::BG_DEFAULT, Message::FLAG_APPEND_NEWLINE | Message::FLAG_PREPEND_DATE, "H:i:s > "))->display();
// 06:42:06 > All arguments in the constructor

```

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/pointybeard/helpers-cli-message/issues),
or better yet, fork the library and submit a pull request.

## Contributing

We encourage you to contribute to this project. Please check out the [Contributing documentation](https://github.com/pointybeard/helpers-cli-message/blob/master/CONTRIBUTING.md) for guidelines about how to get involved.

## License

"PHP Helpers: Command-line Message" is released under the [MIT License](http://www.opensource.org/licenses/MIT).
