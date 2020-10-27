<?php

declare(strict_types=1);

namespace Symphony\Symphony\Widgets;

use Symphony\Symphony;

class Time extends Symphony\AbstractWidget {

    public function __construct(string $dateTimeString = 'now', string $format = __SYM_TIME_FORMAT__, bool $isPublishedDate = false, array $attributes = []) {

        $this
            ->dateTimeString($dateTimeString)
            ->format($format)
            ->isPublishedDate($isPublishedDate)
            ->attributes($attributes)
        ;
    }

    public function toXmlElement(): Symphony\XmlElement
    {

        $dateTimeObj = Symphony\DateTimeObj::parse($this->dateTimeString());

        $output = new Symphony\XmlElement(
            'time',
            Symphony\Lang::localizeDate($dateTimeObj->format($this->format())),
            array_merge(
                $this->attributes(),
                [
                    'datetime' => $dateTimeObj->format(\DateTime::ISO8601),
                    'utc' => $dateTimeObj->format('U')
                ]
            )
        );

        if (true == $this->isPublishedDate()) {
            $output->setAttribute('pubdate', 'pubdate')
        }

        return $output;

    }
}
