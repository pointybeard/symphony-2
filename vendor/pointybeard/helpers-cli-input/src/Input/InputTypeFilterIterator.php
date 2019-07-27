<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input;

class InputTypeFilterIterator extends \FilterIterator {

    private $types;
    private $mode;

    public const FILTER_INCLUDE = 0x0001;
    public const FILTER_EXCLUDE = 0x0002;

    public function __construct(\Iterator $iterator, array $types=[], int $mode=self::FILTER_INCLUDE)
    {
        parent::__construct($iterator);

        $this->types = array_map('strtolower', $types);
        $this->mode = $mode;

    }
    public function accept()
    {
        $input = $this->getInnerIterator()->current();

        switch($this->mode) {
            case self::FILTER_EXCLUDE:
                return !in_array($input->getType(), $this->types);
                break;

            case self::FILTER_INCLUDE:
            default:
                return in_array($input->getType(), $this->types);
                break;

        }
    }
}
