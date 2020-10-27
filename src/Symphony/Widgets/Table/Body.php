<?php

declare(strict_types=1);

namespace Symphony\Symphony\Widgets\Table;

use Symphony\Symphony;

class Body extends Symphony\AbstractWidget
{
    // array $rows, $class = null, $id = null, array $attributes = null
    public function __construct(array $rows, ?string $class = null, ?string $id = null, array $attributes = [])
    {
        $this
            ->rows($rows)
            ->class($class)
            ->id($id)
            ->attributes($attributes)
        ;
    }

    public function toXmlElement(): Symphony\XmlElement
    {
        $output = new Symphony\XmlElement('tbody', null, $this->attributes());

        if (null != $this->class()) {
            $output->setAttribute('class', $this->class());
        }

        if (null != $this->id()) {
            $output->setAttribute('id', $this->id());
        }

        foreach ($this->rows() as $row) {
            if ($row instanceof Symphony\XmlElement) {
                $output->appendChild($row);
            } elseif ($row instanceof Symphony\AbstractWidget) {
                $output->appendChild($row->toXmlElement());
            }
        }

        return $output;
    }
}
