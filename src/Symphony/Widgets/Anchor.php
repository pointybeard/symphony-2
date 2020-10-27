<?php

declare(strict_types=1);

namespace Symphony\Symphony\Widgets;

use Symphony\Symphony;

class Anchor extends Symphony\AbstractWidget
{
    public function __construct($value, string $href, ?string $title = null, ?string $class = null, ?string $id = null, array $attributes = [])
    {
        $this
            ->value($value)
            ->href($href)
            ->title($title)
            ->class($class)
            ->id($id)
            ->attributes($attributes)
        ;
    }

    public function toXmlElement(): Symphony\XmlElement
    {
        $output = new Symphony\XmlElement('a', $this->value(), array_merge(['href' => $this->href()], $this->attributes()));

        if (null != $this->title()) {
            $output->setAttribute('title', $this->title());
        }

        if (null != $this->class()) {
            $output->setAttribute('class', $this->class());
        }

        if (null != $this->id()) {
            $output->setAttribute('id', $this->id());
        }

        return $output;
    }
}
