<?php

declare(strict_types=1);

namespace Symphony\Symphony\Widgets;

use Symphony\Symphony;

class Checkbox extends Symphony\AbstractWidget
{
    public function __construct(string $name, $value, ?string $description = null, ?Symphony\XmlElement &$wrapper = null, ?string $help = null, array $attributes = [])
    {
        $this
            ->name($name)
            ->value($value)
            ->description($description)
            ->wrapper($wrapper)
            ->help($help)
            ->attributes($attributes)
        ;
    }

    public function toXmlElement(): Symphony\XmlElement
    {
        $output = Symphony\WidgetFactory::build('Label')->toXmlElement();

        if (null != $this->help()) {
            $output->addClass('inline-help');
        }

        $defaultHiddenInput = Symphony\WidgetFactory::build('Input', $this->name(), 'no', 'hidden')->toXmlElement();
        if ($this->wrapper() instanceof Symphony\XmlElement) {
            $this->wrapper()->appendChild($defaultHiddenInput);
        } else {
            $output->appendChild($defaultHiddenInput);
        }

        // Include the actual checkbox.
        $checkbox = Symphony\WidgetFactory::build('Input', $this->name(), 'yes', 'checkbox')->toXmlElement();
        if ('yes' === $this->value()) {
            $checkbox->setAttribute('checked', 'checked');
        }

        // Build the checkbox, then label, then help
        $output->setValue(
            __("%s {$this->description()} %s", [$checkbox->generate(), null != $this->help() ? "<i>({$this->help()})</i>" : ''])
        );

        if (false == empty($this->attributes())) {
            $output->setAttributeArray($this->attributes());
        }

        // If a wrapper was given, add the label to it
        if ($this->wrapper() instanceof Symphony\XmlElement) {
            $this->wrapper()->appendChild($output);
        }

        return $output;
    }
}
