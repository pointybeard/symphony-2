<?php

declare(strict_types=1);

namespace Symphony\Symphony\Widgets;

use Symphony\Symphony;

class Input extends Symphony\AbstractWidget
{
    public function __construct(string $name = null, $value = null, string $type = 'text', array $attributes = [])
    {
        $this
            ->name($name)
            ->value($value)
            ->type($type)
            ->attributes($attributes)
        ;
    }

    public function toXmlElement(): Symphony\XmlElement
    {
        $obj = new Symphony\XmlElement('input');
        $obj->setAttribute('name', $this->name());

        if (null != $this->type()) {
            $obj->setAttribute('type', $this->type());
        }

        if (0 !== strlen((string) $this->value())) {
            $obj->setAttribute('value', $this->value());
        }

        if (false == empty($this->attributes())) {
            $obj->setAttributeArray($this->attributes());
        }

        return $obj;
    }
}
