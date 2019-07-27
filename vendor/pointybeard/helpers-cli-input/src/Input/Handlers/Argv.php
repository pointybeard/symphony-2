<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input\Handlers;
use pointybeard\Helpers\Cli\Input\Exceptions;
use pointybeard\Helpers\Cli\Input;
use pointybeard\Helpers\Functions\Flags;
use pointybeard\Helpers\Functions\Debug;

class Argv extends Input\AbstractInputHandler
{
    private $argv = null;

    const OPTION_LONG = 'long';
    const OPTION_SHORT = 'short';
    const ARGUMENT = 'argument';

    public function __construct(array $argv = null)
    {
        if (null === $argv) {
            $argv = $_SERVER['argv'];
        }

        // Remove the script name
        array_shift($argv);

        $this->argv = self::expandOptions($argv);
    }

    /**
     * This will look for combined options, e.g -vlt and expand them to -v -l -t.
     */
    protected static function expandOptions(array $args): array
    {
        $result = [];
        foreach ($args as $a) {
            switch (self::findType($a)) {
                case self::OPTION_SHORT:

                    // If the name is longer than a 2 characters
                    // it will mean it's a combination of flags. e.g.
                    // -vlt 12345 is the same as -v -l -t 12345
                    if (strlen($a) > 2) {
                        // Strip the leading hyphen (-)
                        $a = substr($a, 1);

                        for ($ii = 0; $ii < strlen($a); ++$ii) {
                            $result[] = "-{$a[$ii]}";
                        }

                        break;
                    }

                    // no break
                default:
                    $result[] = $a;
                    break;
            }
        }

        return $result;
    }

    protected function findOptionInCollection(string $name): ?Input\AbstractInputType
    {
        $option = $this->collection->find($name);
        if(!($option instanceof Input\AbstractInputType)) {
            return null;
        }

        return $option;
    }

    protected function findArgumentInCollection(int $index, string $token): ?Input\AbstractInputType
    {
        $arguments = $this->collection->getItemsByType('Argument');
        $position = 0;
        foreach($arguments as $a) {
            if($position == $index) {
                return $a;
            }
            $position++;
        }
        return null;
    }

    protected function parse(): bool
    {
        $it = new \ArrayIterator($this->argv);

        $position = 0;
        $argumentCount = 0;

        while ($it->valid()) {
            $token = $it->current();

            switch (self::findType($token)) {
                case self::OPTION_LONG:
                    $opt = substr($token, 2);

                    if (false !== strstr($opt, '=')) {
                        list($name, $value) = explode('=', $opt, 2);
                    } else {
                        $name = $opt;
                        $value = true;
                    }

                    $o = $this->findOptionInCollection($name);

                    $this->input[
                        $o instanceof Input\AbstractInputType
                            ? $o->name()
                            : $name
                    ] = $value;

                    break;

                case self::OPTION_SHORT:
                    $name = substr($token, 1);

                    // Determine if we're expecting a value.
                    // It also might have a long option equivalent, so we need
                    // to look for that too.
                    $o = $this->findOptionInCollection($name);

                    // This could also be an incrementing value
                    // and needs to be added up. E.g. e.g. -vvv or -v -v -v
                    // would be -v => 3
                    if ($o instanceof Input\AbstractInputType && Flags\is_flag_set($o->flags(), Input\AbstractInputType::FLAG_TYPE_INCREMENTING)) {
                        $value = isset($this->input[$name])
                            ? $this->input[$name] + 1
                            : 1
                        ;

                    // Not incrementing, so resume default behaviour
                    } else {
                        // We'll need to look ahead and see what the next value is.
                        // Ignore it if the next item is another option
                        // Advance the pointer to grab the next value
                        $it->next();
                        $value = $it->current();

                        // See if the next item is another option and of it is,
                        // rewind the iterator and set value to 'true'. Also,
                        // if this option doesn't expect a value (no FLAG_VALUE_REQUIRED or FLAG_VALUE_OPTIONAL flag set), don't capture the next value.
                        if (null === $value || self::isOption($value) || !($o instanceof Input\AbstractInputType) || (
                            !Flags\is_flag_set($o->flags(), Input\AbstractInputType::FLAG_VALUE_REQUIRED) && !Flags\is_flag_set($o->flags(), Input\AbstractInputType::FLAG_VALUE_OPTIONAL)
                        )) {
                            $value = true;
                            $it->seek($position);
                        }
                    }

                    $this->input[
                        $o instanceof Input\AbstractInputType
                            ? $o->name()
                            : $name
                    ] = $value;

                    break;

                case self::ARGUMENT:
                default:
                    // Arguments are positional, so we need to keep a track
                    // of the index and look at the collection for an argument
                    // with the same index
                    $a = $this->findArgumentInCollection($argumentCount, $token);

                    $this->input[
                        $a instanceof Input\AbstractInputType
                            ? $a->name()
                            : $argumentCount
                    ] = $token;
                    ++$argumentCount;
                    break;
            }
            $it->next();
            ++$position;
        }

        return true;
    }

    private static function isOption(string $value): bool
    {
        return '-' == $value[0];
    }

    private static function findType(string $value): string
    {
        if (0 === strpos($value, '--')) {
            return self::OPTION_LONG;
        } elseif (self::isOption($value)) {
            return self::OPTION_SHORT;
        } else {
            return self::ARGUMENT;
        }
    }
}
