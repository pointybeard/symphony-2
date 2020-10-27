<?php

declare(strict_types=1);

namespace Symphony\Symphony\Widgets;

use Symphony\Symphony;

class Textarea extends Symphony\AbstractWidget
{
    public function __construct(string $name, int $rows = 15, int $cols = 50, $value = null, array $attributes = [])
    {
        $this
            ->name($name)
            ->rows($rows)
            ->cols($cols)
            ->value($value)
            ->attributes($attributes)
        ;
    }

    public function toXmlElement(): Symphony\XmlElement
    {
        $output = new Symphony\XmlElement('textarea', $this->value(), array_merge($this->attributes(), [
            'name' => $this->name(),
            'rows' => $this->rows(),
            'cols' => $this->cols(),
        ]));

        $output->setSelfClosingTag(false);

        return $output;
    }
}
