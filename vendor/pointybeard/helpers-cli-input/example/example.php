<?php

declare(strict_types=1);
include __DIR__.'/../vendor/autoload.php';

use pointybeard\Helpers\Cli\Input;
use pointybeard\Helpers\Cli\Colour\Colour;
use pointybeard\Helpers\Functions\Cli;

// Define what we are expecting to get from the command line
$collection = (new Input\InputCollection())
    ->add(
        Input\InputTypeFactory::build('Argument')
            ->name('action')
            ->flags(Input\AbstractInputType::FLAG_REQUIRED)
            ->description('The name of the action to perform')
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
        Input\InputTypeFactory::build('LongOption')
            ->name('data')
            ->short('d')
            ->flags(Input\AbstractInputType::FLAG_OPTIONAL | Input\AbstractInputType::FLAG_VALUE_REQUIRED)
            ->description('Path to the input JSON data')
            ->validator(new Input\Validator(
                function (Input\AbstractInputType $input, Input\AbstractInputHandler $context) {
                    // Make sure -d (--data) is a valid file that can be read
                    $file = $context->find('data');

                    if (!is_readable($file)) {
                        throw new \Exception('The file specified via option --data does not exist or is not readable.');
                    }

                    // Now make sure it is valid JSON
                    try {
                        $json = json_decode(file_get_contents($file), false, 512, JSON_THROW_ON_ERROR);
                    } catch (JsonException $ex) {
                        throw new \Exception(sprintf('The file specified via option --data does not appear to be a valid JSON ddocument. Returned: %s: %s', $ex->getCode(), $ex->getMessage()));
                    }

                    return $json;
                }
            ))
    )
;

// Get the supplied input. Passing the collection will make the handler bind values
// and validate the input according to our collection
try {
    $argv = Input\InputHandlerFactory::build('Argv', $collection);
} catch (\Exception $ex) {
    echo 'Error when attempting to bind values to collection. Returned: '.$ex->getMessage().PHP_EOL;
    exit;
}

// Display the manual in green text
echo Cli\manpage(
    basename(__FILE__),
    '1.0.2',
    'An example script for the PHP Helpers: Command-line Input and Input Type Handlers composer library (pointybeard/helpers-cli-input).',
    $collection,
    Colour::FG_GREEN,
    Colour::FG_WHITE,
    [
        'Examples' => 'php -f example/example.php -- -vvv -d example/example.json import',
    ]
).PHP_EOL.PHP_EOL;

// example.php 1.0.2, An example script for the PHP Helpers: Command-line Input
// and Input Type Handlers composer library (pointybeard/helpers-cli-input).
// Usage: example.php [OPTIONS]... ACTION...
//
// Arguments:
// ACTION              The name of the action to perform
//
// Options:
// -v                            verbosity level. -v (errors only), -vv
//                               (warnings and errors), -vvv (everything).
// -d, --data=VALUE              Path to the input JSON data
//
// Examples:
// php -f example/example.php -- -vvv -d example/example.json import

var_dump($argv->find('action'));
// string(6) "import"

var_dump($argv->find('v'));
//int(3)

var_dump($argv->find('data'));
// class stdClass#11 (1) {
//   public $fruit =>
//   array(2) {
//     [0] =>
//     string(5) "apple"
//     [1] =>
//     string(6) "banana"
//   }
// }

var_dump($argv->find('nope-doesnt-exist'));
// NULL
