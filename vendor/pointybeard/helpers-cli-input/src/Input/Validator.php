<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input;

class Validator implements Interfaces\InputValidatorInterface
{
    private $func;

    public function __construct(\Closure $func)
    {
        // Check the closure used for validation meets requirements
        $params = (new \ReflectionFunction($func))->getParameters();

        // Must have exactly 2 params
        if (2 != count($params)) {
            throw new \Exception('Closure passed to Validator::__construct() is invalid: Must have exactly 2 parameters.');
        }

        // First must be 'input' and be of type pointybeard\Helpers\Cli\Input\AbstractInputType
        if ('input' != $params[0]->getName() || __NAMESPACE__.'\AbstractInputType' != (string) $params[0]->getType()) {
            throw new \Exception('Closure passed to Validator::__construct() is invalid: First parameter must match '.__NAMESPACE__."\AbstractInputType \$input. Provided with ".(string) $params[0]->getType()." \${$params[0]->getName()}");
        }

        // Second must be 'context' and be of type pointybeard\Helpers\Cli\Input\AbstractInputHandler
        if ('context' != $params[1]->getName() || __NAMESPACE__.'\AbstractInputHandler' != (string) $params[1]->getType()) {
            throw new \Exception('Closure passed to Validator::__construct() is invalid: Second parameter must match '.__NAMESPACE__."\AbstractInputHandler \$context. Provided with ".(string) $params[1]->getType()." \${$params[1]->getName()}");
        }

        $this->func = $func;
    }

    public function validate(AbstractInputType $input, AbstractInputHandler $context)
    {
        return ($this->func)($input, $context);
    }
}
