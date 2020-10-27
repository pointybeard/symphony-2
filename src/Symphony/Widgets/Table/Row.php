<?php

declare(strict_types=1);

namespace Symphony\Symphony\Widgets\Table;

use Symphony\Symphony;

class Row extends Symphony\AbstractWidget
{
    // array $cells, $class = null, $id = null, $rowspan = null, array $attributes = null
    public function __construct(array $cells, ?string $class = null, ?string $id = null, ?int $rowspan = null, array $attributes = [])
    {
        $this
            ->cells($cells)
            ->rowspan($rowspan)
            ->class($class)
            ->id($id)
            ->attributes($attributes)
        ;
    }

    public function toXmlElement(): Symphony\XmlElement
    {
        $output = new Symphony\XmlElement('tr', null, $this->attributes());

        if (null != $this->rowspan()) {
            $output->setAttribute('rowspan', $this->rowspan());
        }

        if (null != $this->id()) {
            $output->setAttribute('id', $this->id());
        }

        if (null != $this->class()) {
            $output->setAttribute('class', $this->class());
        }

        foreach ($this->cells() as $cell) {
            if ($cell instanceof Symphony\XmlElement) {
                $output->appendChild($cell);
            } elseif ($cell instanceof Symphony\AbstractWidget) {
                $output->appendChild($cell->toXmlElement());
            }
        }

        return $output;
    }
}
