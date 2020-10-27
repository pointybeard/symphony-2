<?php

namespace Symphony\Symphony;

/**
 * Creates a filter that only returns XMLElement items.
 */
class XmlElementChildrenFilter extends \FilterIterator
{
    public function accept()
    {
        $item = $this->getInnerIterator()->current();

        return $item instanceof \XMLElement;
    }
}
