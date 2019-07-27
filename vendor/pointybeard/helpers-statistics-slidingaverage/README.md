# PHP Helpers: Sliding Average

-   Version: v1.0.2
-   Date: May 05 2019
-   [Release notes](https://github.com/pointybeard/helpers-statistics-slidingaverage/blob/master/CHANGELOG.md)
-   [GitHub repository](https://github.com/pointybeard/helpers-statistics-slidingaverage)

Ring buffer for calculating average of a changing set; helps to reduce jitter.

## Installation

This library is installed via [Composer](http://getcomposer.org/). To install, use `composer require pointybeard/helpers-statistics-slidingaverage` or add `"pointybeard/helpers-statistics-slidingaverage": "~1.0"` to your `composer.json` file.

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

### Requirements

There are no particuar requirements for this library other than PHP 5.6 or greater.

To include all the [PHP Helpers](https://github.com/pointybeard/helpers) packages on your project, use `composer require pointybeard/helpers` or add `"pointybeard/helpers": "~1.0"` to your composer file.

## Usage

Simple example of how to use the SlidingAverage class:

```php
<?php

include __DIR__ . "/vendor/autoload.php";

use pointybeard\Helpers\Statistics\SlidingAverage;

$average = new SlidingAverage\SlidingAverage(20, 0);

do {

    // Do some work here
    $value = rand(0, 100);

    // Push some value into the ring buffer, e.g. time elapsed etc
    $average->push($value);

    echo "\rAverage is: " . $average->sample();
    usleep(70000);

} while(true);
```

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/pointybeard/helpers-statistics-slidingaverage/issues),
or better yet, fork the library and submit a pull request.

## Contributing

We encourage you to contribute to this project. Please check out the [Contributing documentation](https://github.com/pointybeard/helpers-statistics-slidingaverage/blob/master/CONTRIBUTING.md) for guidelines about how to get involved.

## Credits

Credit to Alexey Volynskov's tutorial, and C# implementation, [Quick Tip: Use the "Ring Buffer" Data Structure to Smooth Jittery Values](https://gamedevelopment.tutsplus.com/tutorials/quick-tip-use-the-ring-buffer-data-structure-to-smooth-jittery-values--gamedev-14373). The inspiration for this class comes from that tutorial.

## License

"PHP Helpers: Sliding Average" is released under the [MIT License](http://www.opensource.org/licenses/MIT).
