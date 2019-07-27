# PHP Helpers: Readable Trace Exception

-   Version: v1.0.2
-   Date: May 26 2019
-   [Release notes](https://github.com/pointybeard/helpers-exceptions-readabletrace/blob/master/CHANGELOG.md)
-   [GitHub repository](https://github.com/pointybeard/helpers-exceptions-readabletrace)

Provides an exception base class that includes method `getReadableTrace()`, giving simple access to a plain-text, readable, backtrace string.

## Installation

This library is installed via [Composer](http://getcomposer.org/). To install, use `composer require pointybeard/helpers-exceptions-readabletrace` or add `"pointybeard/helpers-exceptions-readabletrace": "~1.0"` to your `composer.json` file.

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

### Requirements

This library makes use of the [PHP Helpers: Path Functions](https://github.com/pointybeard/helpers-functions-paths) (`pointybeard/helpers-functions-paths`), [PHP Helpers: Debug Functions](https://github.com/pointybeard/helpers-functions-debug) (`pointybeard/helpers-functions-debug`), and [PHP Helpers: String Functions](https://github.com/pointybeard/helpers-functions-strings) (`pointybeard/helpers-functions-strings`). They are installed automatically via composer.

To include all the [PHP Helpers](https://github.com/pointybeard/helpers) packages on your project, use `composer require pointybeard/helpers` or add `"pointybeard/helpers": "~1.1"` to your composer file.

## Usage

Here is an example of how to use the `ReadableTraceException` base class:

```php
<?php

declare(strict_types=1);
include __DIR__.'/vendor/autoload.php';
use pointybeard\Helpers\Exceptions\ReadableTrace\ReadableTraceException;

class foo
{
    public function __construct()
    {
        // Go a little deeper so there is more to show in the backtrace
        $this->someMethod();
    }

    private function someMethod()
    {
        // Do some work. Trigger an error.
        throw new ReadableTraceException('Oh oh, something went wrong.');
    }
}

try {
    // Do some work here
    $f = new Foo();
} catch (ReadableTraceException $ex) {
    // Template for displaying the exception to the screen
    $message = <<<OUTPUT
[%s]
An error occurred around line %d of %s. The following was returned:

%s

Backtrace
==========
%s

OUTPUT;

    printf(
        $message,
        (new \ReflectionClass($ex))->getName(),
        $ex->getLine(),
        $ex->getFile(),
        $ex->getMessage(),
        $ex->getReadableTrace()
    );
    // [pointybeard\Helpers\Exceptions\ReadableTrace\ReadableTraceException]
    // An error occurred around line 18 of /path/to/test.php. The following was returned:
    //
    // Oh oh, something went wrong.
    //
    // Backtrace
    // ==========
    // [test.php:12] foo->someMethod();
    // [test.php:24] foo->__construct();
}

```

### Placeholders

The format of each trace line can be modified by setting the `format` argument when calling `ReadableTraceException::getReadableTrace()`. The default format is `[{{PATH}}/{{FILENAME}}:{{LINE}}] {{CLASS}}{{TYPE}}{{FUNCTION}}();` which looks something like `[../path/to/test.php:24] foo->__construct();`.

Placeholders available are provided by [PHP Helpers: Debug Functions](https://github.com/pointybeard/helpers-functions-debug).

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/pointybeard/helpers-exceptions-readabletrace/issues),
or better yet, fork the library and submit a pull request.

## Contributing

We encourage you to contribute to this project. Please check out the [Contributing documentation](https://github.com/pointybeard/helpers-exceptions-readabletrace/blob/master/CONTRIBUTING.md) for guidelines about how to get involved.

## License

"PHP Helpers: Readable Trace Exception" is released under the [MIT License](http://www.opensource.org/licenses/MIT).
