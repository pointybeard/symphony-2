<?php

declare(strict_types=1);

namespace Symphony\Symphony;

abstract class AbstractWidget implements Interfaces\WidgetInterface
{
    protected $properties = [];

    public function __call(string $name, array $args)
    {
        if (true == empty($args)) {
            return $this->$name;
        }

        $this->$name = $args[0];

        return $this;
    }

    public function __get($name)
    {
        return $this->properties[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->properties[$name] = $value;
    }
}
