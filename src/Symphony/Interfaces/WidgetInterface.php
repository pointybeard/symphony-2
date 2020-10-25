<?php

declare(strict_types=1);

namespace Symphony\Symphony\Interfaces;

use Symphony\Symphony;

interface WidgetInterface {
    public function toXmlElement(): Symphony\XmlElement;
}
