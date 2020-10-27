<?php

declare(strict_types=1);

namespace Symphony\Symphony\Widgets;

use Symphony\Symphony;

class Apply extends Symphony\AbstractWidget
{
    // array $options = null
    public function __construct(?array $options = [], array $attributes = [])
    {
        $this
            ->options($options)
            ->attributes($attributes)
        ;
    }

    public function toXmlElement(): Symphony\XmlElement
    {
        $output = new Symphony\XmlElement(
            'fieldset',
            null,
            array_merge(['class' => 'apply'], $this->attributes())
        );

        $div = new Symphony\XmlElement('div');
        $div->appendChild(
            Symphony\WidgetFactory::build(
                'Label',
                __('Actions'),
                null,
                'accessible',
                null,
                [
                    'for' => 'with-selected',
                ]
            )->toXmlElement()
        );
        $div->appendChild(
            Symphony\WidgetFactory::build(
                'Select',
                'with-selected',
                $this->options(),
                [
                    'id' => 'with-selected',
                ]
            )->toXmlElement()
        );
        $output->appendChild($div);
        $output->appendChild(new Symphony\XmlElement('button', __('Apply'), ['name' => 'action[apply]', 'type' => 'submit']));

        return $output;
    }
}
