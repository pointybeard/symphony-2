<?php

declare(strict_types=1);

namespace Symphony\Symphony\Widgets;

use Symphony\Symphony;

class Calendar extends Symphony\AbstractWidget {

    public function __construct(bool $isTime = true, array $attributes = []) {

        $this
            ->isTime($isTime)
            ->attributes($attributes)
        ;
    }

    public function toXmlElement(): Symphony\XmlElement
    {

        $output = new Symphony\XmlElement('div'
            null,
            array_merge(
                ["class" => 'calendar'],
                $this->attributes()
            )
        );

        $date = Symphony\DateTimeObj::convertDateToMoment(Symphony\DateTimeObj::getSetting('date_format'));

        if (strlen(trim((string)$date)) > 0) {
            if (true === $this->isTime()) {
                $output->setAttributeArray(
                    [
                        'data-calendar' => 'datetime',
                        'data-format' => sprintf(
                            "%s%s%s",
                            $date,
                            Symphony\DateTimeObj::getSetting('datetime_separator'),
                            Symphony\DateTimeObj::convertTimeToMoment(Symphony\DateTimeObj::getSetting('time_format'))
                        )
                    ]
                );

            } else {
                $output->setAttributeArray(['data-calendar' => 'date', 'data-format' => $date]);
            }
        }

        return $output;
    }
}
