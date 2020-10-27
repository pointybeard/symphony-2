<?php

declare(strict_types=1);

namespace Symphony\Symphony\Widgets;

use Symphony\Symphony;

class Form extends Symphony\AbstractWidget
{
    public const METHOD_POST = 'post';
    public const METHOD_GET = 'get';

    public function __construct(?string $action = null, ?string $method = self::METHOD_POST, ?string $class = null, ?string $id = null, array $attributes = [])
    {
        $this
            ->action($action)
            ->method($method)
            ->class($class)
            ->id($id)
            ->attributes($attributes)
        ;
    }

    public function toXmlElement(): Symphony\XmlElement
    {
        $output = new Symphony\XmlElement(
            'form',
            null,
            array_merge(
                [
                    'action' => $this->action(),
                    'method' => $this->method(),
                ],
                $this->attributes()
            )
        );

        if (null != $this->class()) {
            $output->setAttribute('class', $this->class());
        }

        if (null != $this->id()) {
            $output->setAttribute('id', $this->id());
        }

        return $output;
    }
}
