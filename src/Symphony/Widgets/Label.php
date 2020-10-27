<?php

declare(strict_types=1);

namespace Symphony\Symphony\Widgets;

use Symphony\Symphony;

class Label extends Symphony\AbstractWidget
{
    public function __construct(?string $name = null, ?Symphony\XmlElement $child = null, ?string $class = null, ?string $id = null, array $attributes = [])
    {
        $this
            ->name($name)
            ->child($child)
            ->class($class)
            ->id($id)
            ->attributes($attributes)
        ;
    }

    public function toXmlElement(): Symphony\XmlElement
    {
        $obj = new Symphony\XmlElement('label', $this->name());

        if ($this->child() instanceof Symphony\XmlElement) {
            $obj->appendChild($this->child());
        }

        if (null != $this->class()) {
            $obj->setAttribute('class', $this->class());
        }

        if (null != $this->id()) {
            $obj->setAttribute('id', $this->id());
        }

        if (false == empty($this->attributes())) {
            $obj->setAttributeArray($this->attributes());
        }

        return $obj;
    }
}
