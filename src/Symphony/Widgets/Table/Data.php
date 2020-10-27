<?php

declare(strict_types=1);

namespace Symphony\Symphony\Widgets\Table;

use Symphony\Symphony;

class Data extends Symphony\AbstractWidget
{
    // $value, $class = null, $id = null, $colspan = null, array $attributes = null
    public function __construct($value, ?string $class = null, ?string $id = null, ?int $colspan = null, array $attributes = [])
    {
        $this
            ->value($value)
            ->colspan($colspan)
            ->class($class)
            ->id($id)
            ->attributes($attributes)
        ;
    }

    public function toXmlElement(): Symphony\XmlElement
    {
        $output = new Symphony\XmlElement('td', null, $this->attributes());

        if (is_object($this->value())) {
            if ($this->value() instanceof Symphony\XmlElement) {
                $output->appendChild($this->value());
            } elseif ($this->value() instanceof Symphony\AbstractWidget) {
                $output->appendChild($this->value()->toXmlElement());
            } else {
                throw new Symphony\Exceptions\SymphonyException('Value supplied is unknown object. Must be instance of XmlElement or AbstractWidget');
            }
        } else {
            $output->setValue($this->value());
        }

        if (null != $this->class()) {
            $output->setAttribute('class', $this->class());
        }

        if (null != $this->id()) {
            $output->setAttribute('id', $this->id());
        }

        if (null != $this->colspan()) {
            $output->setAttribute('colspan', $this->colspan());
        }

        if (is_array($attributes) && !empty($attributes)) {
            $td->setAttributeArray($attributes);
        }

        return $output;
    }
}
