<?php

declare(strict_types=1);

namespace Symphony\Symphony\Widgets;

use Symphony\Symphony;

class Error extends Symphony\AbstractWidget
{
    public function __construct(Symphony\XmlElement $element, string $message, array $attributes = [])
    {
        $this
            ->element($element)
            ->message($message)
            ->attributes($attributes)
        ;
    }

    public function toXmlElement(): Symphony\XmlElement
    {
        $output = new Symphony\XmlElement('div', null, array_merge(['class' => 'invalid'], $this->attributes()));

        $output->appendChild($this->element());
        $output->appendChild(new Symphony\XmlElement('p', $this->message()));

        return $output;
    }
}
