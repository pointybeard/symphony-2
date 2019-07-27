<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input;

class InputCollection implements \Iterator, \Countable
{
    private $items = [];
    private $position = 0;

    public const POSITION_APPEND = 0x0001;
    public const POSITION_PREPEND = 0x0002;

    // Prevents the class from being instanciated
    public function __construct()
    {
        $this->position = 0;
    }

    public function current(): mixed
    {
        return $this->items[$this->position];
    }

    public function key(): scalar
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }

    public function count() : int {
        return count($this->items);
    }

    public function exists(string $name, &$index=null): bool {
        return (null !== $this->find($name, null, null, $index));
    }

    public function remove(string $name): self {
        if(!$this->exists($name, $index)) {
            throw new \Exception("Input '{$name}' does not exist in this collection");
        }
        unset($this->items[$index]);
        return $this;
    }

    public function add(Interfaces\InputTypeInterface $input, bool $replace = false, int $position=self::POSITION_APPEND): self
    {
        if($this->exists($input->name(), $index) && !$replace) {
            throw new \Exception(
                (new \ReflectionClass($input))->getShortName()." '{$input->name()}' already exists in this collection"
            );
        }

        if (true == $replace && null !== $index) {
            $this->items[$index] = $input;
        } else {
            if($position == self::POSITION_PREPEND) {
                array_unshift($this->items, $input);
            } else {
                array_push($this->items, $input);
            }
        }

        return $this;
    }

    public function find(string $name, array $restrictToType = null, array $excludeType = null, &$index = null): ?AbstractInputType
    {
        foreach ($this->items as $index => $input) {
            // Check if we're restricting to or excluding specific types
            if (null !== $restrictToType && !in_array($input->getType(), $restrictToType)) {
                continue;
            } elseif (null !== $excludeType && in_array($input->getType(), $excludeType)) {
                continue;
            }

            if ($input->respondsTo($name)) {
                return $input;
            }

        }
        $index = null;
        return null;
    }

    public function getTypes(): array
    {
        $types = [];
        foreach($this->items as $input) {
            $types[] = $input->getType();
        }
        return array_unique($types);
    }

    public function getItems(): \Iterator
    {
        return (new \ArrayObject($this->items))->getIterator();
    }

    public function getItemsByType(string $type): \Iterator
    {
        return new InputTypeFilterIterator(
            $this->getItems(),
            [$type],
            InputTypeFilterIterator::FILTER_INCLUDE
        );
    }

    public function getItemsExcludeByType(string $type): \Iterator
    {
        return new InputTypeFilterIterator(
            $this->getItems(),
            [$type],
            InputTypeFilterIterator::FILTER_EXCLUDE
        );
    }

    public function getItemByIndex(int $index): ?AbstractInputType
    {
        return $this->items[$index] ?? null;
    }

    public static function merge(self ...$collections): self
    {

        $iterator = new \AppendIterator;
        foreach ($collections as $c) {
            $iterator->append($c->getItems());
        }

        $mergedCollection = new self();
        foreach ($iterator as $input) {
            $mergedCollection->add($input, true);
        }
        return $mergedCollection;
    }
}
