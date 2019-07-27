# PHP Helpers: Command-line Prompt for Input

-   Version: v1.0.0
-   Date: May 16 2019
-   [Release notes](https://github.com/pointybeard/helpers-cli-prompt/blob/master/CHANGELOG.md)
-   [GitHub repository](https://github.com/pointybeard/helpers-cli-prompt)

Class for asking for input on the command-line

## Installation

This library is installed via [Composer](http://getcomposer.org/). To install, use `composer require pointybeard/helpers-cli-prompt` or add `"pointybeard/helpers-cli-prompt": "~1.0"` to your `composer.json` file.

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

### Requirements

This library makes use of the [PHP Helpers: Flag Functions](https://github.com/pointybeard/helpers-functions-flags) (`pointybeard/helpers-functions-flags`), [PHP Helpers: Command-line Functions](https://github.com/pointybeard/helpers-functions-cli) (`pointybeard/helpers-functions-cli`), and [PHP Helpers: Command-line Message](https://github.com/pointybeard/helpers-cli-message) (`pointybeard/helpers-cli-message`) packages. They are installed automatically via composer.

To include all the [PHP Helpers](https://github.com/pointybeard/helpers) packages on your project, use `composer require pointybeard/helpers` or add `"pointybeard/helpers": "~1.0"` to your composer file.

## Usage

Include this library in your PHP files with `use pointybeard\Helpers\Cli\Prompt` and instanciate the `Prompt\Prompt` class like so:

```php
<?php

include __DIR__ . "/vendor/autoload.php";

use pointybeard\Helpers\Cli\Prompt\Prompt;
use pointybeard\Helpers\Cli\Message\Message;
use pointybeard\Helpers\Cli\Colour\Colour;

// The most basic of prompt
$name = (new Prompt("Enter your name"))->display();
// Enter your name:

// Prompt with a default value
$proceed = (new Prompt("Proceed with installation?"))
    ->default('yes')
    ->display()
;
// Proceed with installation? [yes]:

// A prompt that does not echo the value as it is typed
$password = (new Prompt)
    ->prompt("Enter password")
    ->flags(Prompt::FLAG_SILENT)
    ->display()
;

// Prompt with a custom Cli/Message/Message object instead of a string
$value = (new Prompt)
    ->prompt((new Message)
        ->message("Some fancy looking prompt")
        ->foreground(Colour::FG_BLACK)
        ->background(Colour::BG_YELLOW)
        ->flags(NULL)
    )
    ->display()
;

// Validate the input
$emailAddress = (new Prompt("Enter Email Address"))
    ->validator(function($input) {
        if(strlen(trim($input)) <= 0) {
            (new Message)
                ->message("Email address is required!")
                ->foreground(Colour::FG_WHITE)
                ->background(Colour::BG_RED)
                ->display()
            ;
            return false;
        }
        elseif(!strpos($input, "@")) {
            (new Message)
                ->message("Email address is invalid!")
                ->foreground(Colour::FG_WHITE)
                ->background(Colour::BG_RED)
                ->display()
            ;
            return false;
        }
        return true;
    })
    ->display()
;

```

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/pointybeard/helpers-cli-prompt/issues),
or better yet, fork the library and submit a pull request.

## Contributing

We encourage you to contribute to this project. Please check out the [Contributing documentation](https://github.com/pointybeard/helpers-cli-prompt/blob/master/CONTRIBUTING.md) for guidelines about how to get involved.

## License

"PHP Helpers: Command-line Prompt for Input" is released under the [MIT License](http://www.opensource.org/licenses/MIT).
