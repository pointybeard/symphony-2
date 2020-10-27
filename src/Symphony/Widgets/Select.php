<?php

declare(strict_types=1);

namespace Symphony\Symphony\Widgets;

use Symphony\Symphony;

class Select extends Symphony\AbstractWidget
{
    // string $name, array $options = null, array $attributes = null
    public function __construct(string $name, ?array $options = [], array $attributes = [])
    {
        $this
            ->name($name)
            ->options($options)
            ->attributes($attributes)
        ;
    }

    /*
     * @param array  $options    (optional)
     *                           An array containing the data for each `<option>` for this
     *                           `<select>`. If the array is associative, it is assumed that
     *                           `<optgroup>` are to be created, otherwise it's an array of the
     *                           containing the option data. If no options are provided an empty
     *                           `<select>` XmlElement is returned.
     *
     *                           array(
     *                              array($value, $selected, $desc, $class, $id, $attr)
     *                           )
     *
     *                           array(
     *                              array('label' => 'Optgroup', 'data-label' => 'optgroup', 'options' = array(
     *                                  array($value, $selected, $desc, $class, $id, $attr)
     *                              )
     *                           )
     */
    public function toXmlElement(): Symphony\XmlElement
    {
        $output = new Symphony\XmlElement('select', null, array_merge(['name' => $this->name()], $this->attributes()));
        $output->setSelfClosingTag(false);

        if (true == empty($this->options()) && true == array_key_exists('disabled', $this->attributes())) {
            $output->setAttribute('disabled', 'disabled');
        } elseif (false == empty($this->options())) {
            foreach ($this->options() as $o) {
                // This is an optgroup
                if (false == is_array($o)) {
                    throw new Symphony\Exceptions\SymphonyException('All select items must be an instance of XmlElement, or and instance of AbstractWidget, or an array of values.');
                } elseif (true == array_key_exists('label', $o)) {
                    $optgroup = new Symphony\XmlElement('optgroup', null, ['label' => $o['label']]);

                    if (true == isset($o['data-label'])) {
                        $optgroup->setAttribute('data-label', $o['data-label']);
                    }

                    foreach ($o['options'] as $option) {
                        if ($option instanceof Symphony\XmlElement) {
                            $optgroup->appendChild($option);
                        } elseif ($option instanceof Symphony\AbstractWidget) {
                            $optgroup->appendChild($option->toXmlElement());
                        } elseif (true == is_array($option)) {
                            $optgroup->appendChild($this->selectBuildOption(...$option));
                        } else {
                            throw new Symphony\Exceptions\SymphonyException('All select items must be an instance of XmlElement, or and instance of AbstractWidget, or an array of values.');
                        }
                    }

                    $output->appendChild($optgroup);

                // Not and optgroup, just a regular item
                } else {
                    $output->appendChild($this->selectBuildOption(...$o));
                }
            }
        }

        return $output;
    }

    /**
     * This function is used internally by the `self::Select()` to build
     * an XmlElement of an `<option>` from an array.
     *
     * @param array $option
     *                      An array containing the data a single `<option>` for this
     *                      `<select>`. The array can contain the following params:
     *                      string $value
     *                      The value attribute of the resulting `<option>`
     *                      boolean $selected
     *                      Whether this `<option>` should be selected
     *                      string $desc (optional)
     *                      The text of the resulting `<option>`. If omitted $value will
     *                      be used a default.
     *                      string $class (optional)
     *                      The class attribute of the resulting `<option>`
     *                      string $id (optional)
     *                      The id attribute of the resulting `<option>`
     *                      array $attributes (optional)
     *                      Any additional attributes can be included in an associative
     *                      array with the key being the name and the value being the
     *                      value of the attribute. Attributes set from this array
     *                      will override existing attributes set by previous params.
     *                      `array(
     *                      array('one-shot', false, 'One Shot', 'my-option')
     *                      )`
     *
     * @return XMLElement
     */
    private function selectBuildOption($value, ?bool $isSelected = false, $desc = null, ?string $class = null, ?string $id = null, array $attributes = []): Symphony\XmlElement
    {
        if (null == $desc) {
            $desc = $value;
        }

        $output = new Symphony\XmlElement('option', $desc, array_merge($attributes, ['value' => $value]));
        $output->setSelfClosingTag(false);

        if (null != $class) {
            $output->setAttribute('class', $class);
        }

        if (null != $id) {
            $output->setAttribute('id', $id);
        }

        if (true == is_bool($isSelected) && true == $isSelected) {
            $output->setAttribute('selected', 'selected');
        }

        return $output;
    }
}
