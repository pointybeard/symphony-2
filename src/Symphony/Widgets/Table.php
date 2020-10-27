<?php

declare(strict_types=1);

namespace Symphony\Symphony\Widgets;

use Symphony\Symphony;

class Table extends Symphony\AbstractWidget
{
    public function __construct(?object $header = null, ?object $footer = null, ?object $body = null, $class = null, $id = null, array $attributes = [])
    {
        $this
            ->header($header)
            ->footer($footer)
            ->body($body)
            ->class($class)
            ->id($id)
            ->attributes($attributes)
        ;
    }

    public function toXmlElement(): Symphony\XmlElement
    {
        $output = new Symphony\XmlElement('table', null, $this->attributes());

        if (null != $this->class()) {
            $output->setAttribute('class', $this->class());
        }

        if (null != $this->id()) {
            $output->setAttribute('id', $this->id());
        }

        if ($this->header() instanceof Symphony\XmlElement) {
            $output->appendChild($this->header());
        } elseif ($this->header() instanceof self) {
            $output->appendChild($this->header()->toXmlElement());
        }

        if ($this->footer() instanceof Symphony\XmlElement) {
            $output->appendChild($this->footer());
        } elseif ($this->footer() instanceof Symphony\AbstractWidget) {
            $output->appendChild($this->footer()->toXmlElement());
        }

        if ($this->body() instanceof Symphony\XmlElement) {
            $output->appendChild($this->body());
        } elseif ($this->body() instanceof Symphony\AbstractWidget) {
            $output->appendChild($this->body()->toXmlElement());
        }

        return $output;
    }
}
