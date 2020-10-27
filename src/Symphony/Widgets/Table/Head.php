<?php

declare(strict_types=1);

namespace Symphony\Symphony\Widgets\Table;

use Symphony\Symphony;

class Head extends Symphony\AbstractWidget
{
    /*
     * @param array $columns
     *                       An array of column arrays, where the first item is the name of the
     *                       column, the second is the scope attribute, and the third is an array
     *                       of possible attributes.
     *                       `
     *                       [
     *                          ['Column Name', 'scope', ['class'=>'th-class']]
     *                       ]
     */
    public function __construct(array $columns = [], array $attributes = [])
    {
        $this
            ->columns($columns)
            ->attributes($attributes)
        ;
    }

    public function toXmlElement(): Symphony\XmlElement
    {
        $output = new Symphony\XmlElement('thead', null, $this->attributes());
        $tr = new Symphony\XmlElement('tr');

        foreach ($this->columns() as $c) {
            $th = new Symphony\XmlElement('th');

            [$name, $scope, $attributes] = $c;

            if ($name instanceof Symphony\XmlElement) {
                $th->appendChild($name);
            } elseif ($name instanceof self) {
                $th->appendChild($name->toXmlElement());
            } else {
                $th->setValue($name);
            }

            if (null != $scope && strlen(trim((string) $scope)) > 0) {
                $th->setAttribute('scope', $scope);
            }

            if (false == empty($attributes)) {
                $th->setAttributeArray($attributes);
            }

            $tr->appendChild($th);
        }

        $output->appendChild($tr);

        return $output;
    }
}
