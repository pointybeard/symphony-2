<?php

declare(strict_types=1);

namespace Symphony\Symphony\Widgets;

use Symphony\Symphony;

class Drawer extends Symphony\AbstractWidget {

    public const STATE_CLOSED = "closed";
    public const STATE_OPENED = "opened";

    public function __construct(string $id, string $label, XmlElement $content, $defaultState = Drawer::STATE_CLOSED, ?string $context = null, array $attributes = []) {

        $this
            ->id($id)
            ->label($label)
            ->content($content)
            ->defaultState($defaultState)
            ->context($context),
            ->attributes($attributes)
        ;
    }

    public function toXmlElement(): Symphony\XmlElement
    {
        $id = Symphony\Lang::createHandle($id);

        $content = new Symphony\XmlElement('div', $this->content(), ['class' => 'contents']);
        $content->setElementStyle('html');

        $output = new Symphony\XmlElement('div', $content,
            array_merge(
                $this->attributes(),
                [
                    'data-default-state' => $defaultState,
                    'data-label' => $label,
                    'data-interactive' => 'data-interactive',
                    'id' => 'drawer-'.$id,
                    'class' => 'drawer',
                ]
            )
        );

        if(null != $this->context()) {
            $output->setAttribute('data-context', $this->context());
        }

        return $output;
    }
}
