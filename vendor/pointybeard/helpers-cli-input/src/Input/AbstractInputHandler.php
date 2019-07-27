<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input;

use pointybeard\Helpers\Functions\Flags;
use pointybeard\Helpers\Functions\Debug;

abstract class AbstractInputHandler implements Interfaces\InputHandlerInterface
{
    /**
     * Will skip all validation when bind() is executed. Ignores all other flags
     * @var integer
     */
    const FLAG_BIND_SKIP_VALIDATION = 0x0001;

    /**
     * Will skip the required input and required values check
     * @var integer
     */
    const FLAG_VALIDATION_SKIP_REQUIRED = 0x0002;

    /**
     * Will skip running custom validators
     * @var integer
     */
    const FLAG_VALIDATION_SKIP_CUSTOM = 0x0004;

    /**
     * Will skip checking if an input is in the collection
     * @var integer
     */
    const FLAG_VALIDATION_SKIP_UNRECOGNISED = 0x0008;

    protected $input = [];
    protected $collection = null;

    abstract protected function parse(): bool;

    final public function bind(InputCollection $inputCollection, ?int $flags = null): bool
    {
        // Do the binding stuff here
        $this->input = [];
        $this->collection = $inputCollection;

        $this->parse();

        if (!Flags\is_flag_set($flags, self::FLAG_BIND_SKIP_VALIDATION)) {
            $this->validate($flags);
        }

        return true;
    }

    private static function checkRequiredAndRequiredValue(AbstractInputType $input, array $context): void
    {
        if (!isset($context[$input->name()])) {
            if (Flags\is_flag_set($input->flags(), AbstractInputType::FLAG_REQUIRED)) {
                throw new Exceptions\RequiredInputMissingException($input);
            }
        } elseif (Flags\is_flag_set($input->flags(), AbstractInputType::FLAG_VALUE_REQUIRED) && (null == $context[$input->name()] || true === $context[$input->name()])) {
            throw new Exceptions\RequiredInputMissingValueException($input);
        }
    }

    protected function validateInput(AbstractInputType $input, ?int $flags) {
        if(!Flags\is_flag_set($flags, self::FLAG_VALIDATION_SKIP_REQUIRED)) {
            self::checkRequiredAndRequiredValue($input, $this->input);
        }
        // There is a default value, input has not been set, and there
        // is no validator
        if (
            null !== $input->default() &&
            null === $this->find($input->name()) &&
            null === $input->validator()
        ) {
            $result = $input->default();

        // Input has been set and it has a validator. Skip this if
        // FLAG_VALIDATION_SKIP_CUSTOM is set
        } elseif (null !== $this->find($input->name()) && null !== $input->validator() && !Flags\is_flag_set($flags, self::FLAG_VALIDATION_SKIP_CUSTOM)) {
            $validator = $input->validator();

            if ($validator instanceof \Closure) {
                $validator = new Validator($validator);
            } elseif (!($validator instanceof Validator)) {
                throw new \Exception("Validator for '{$input->name()}' must be NULL or an instance of either Closure or Input\Validator.");
            }

            try {
                $result = $validator->validate($input, $this);
            } catch (\Exception $ex) {
                throw new Exceptions\InputValidationFailedException($input, 0, $ex);
            }

            // No default, no validator, but may or may not have been set
        } else {
            $result = $this->find($input->name());
        }

        return $result;
    }

    protected function isInputRecognised(string $name): bool {
        return null === $this->collection->find($name) ? false : true;
    }

    final public function validate(?int $flags = null): void
    {
        if(!Flags\is_flag_set($flags, self::FLAG_VALIDATION_SKIP_UNRECOGNISED)) {
            foreach($this->input as $name => $value) {
                if(false == static::isInputRecognised((string)$name)) {
                    throw new Exceptions\UnrecognisedInputException("'{$name}' is not recognised");
                }
            }
        }

        foreach ($this->collection->getItems() as $input) {
            $this->input[$input->name()] = static::validateInput($input, $flags);
        }
    }

    final public function find(string $name)
    {
        if (isset($this->input[$name])) {
            return $this->input[$name];
        }

        // Check the collection to see if anything responds to $name
        foreach ($this->collection->getItems() as $item) {
            if ($item->respondsTo($name) && isset($this->input[$item->name()])) {
                return $this->input[$item->name()];
            }
        }

        return null;
    }

    final public function getInput(): array
    {
        return $this->input;
    }

    final public function getCollection(): ?InputCollection
    {
        return $this->collection;
    }
}
