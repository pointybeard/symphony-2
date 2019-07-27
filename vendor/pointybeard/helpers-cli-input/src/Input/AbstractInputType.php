<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Cli\Input;

abstract class AbstractInputType implements Interfaces\InputTypeInterface
{
    protected static $type;

    protected $name;
    protected $flags;
    protected $description;
    protected $validator;
    protected $default;

    protected $value;

    public function __construct(string $name = null, int $flags = null, string $description = null, object $validator = null, $default = null)
    {
        $this->name = $name;
        $this->flags = $flags;
        $this->description = $description;
        $this->validator = $validator;
        $this->default = $default;
    }

    public function __call($name, array $args = [])
    {
        if (empty($args)) {
            return $this->$name;
        }

        $this->$name = $args[0];

        return $this;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function respondsTo(string $name): bool
    {
        return $name == $this->name;
    }

    public function getType(): string
    {
        return strtolower((new \ReflectionClass(static::class))->getShortName());
    }
}
