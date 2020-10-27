<?php

namespace Symphony\Symphony;

/**
 * Widget is a utility class that offers a number miscellaneous of
 * functions to help generate common HTML Forms elements as XMLElement
 * objects for inclusion in Symphony backend pages.
 */
abstract class Widget
{
    /**
     * @todo: Investigate why this isn't working correctly.
     **/
    // public static function __callStatic(string $name, array $arguments) {
    //     return WidgetFactory::build(string $name, ...$arguments)->toXmlElement();
    // }

    /**
     * Generates a XmlElement representation of `<label>`.
     *
     * @param string     $name       (optional)
     *                               The text for the resulting `<label>`
     * @param XmlElement $child      (optional)
     *                               An XmlElement that this <label> will become the parent of.
     *                               Commonly used with `<input>`.
     * @param string     $class      (optional)
     *                               The class attribute of the resulting `<label>`
     * @param string     $id         (optional)
     *                               The id attribute of the resulting `<label>`
     * @param array      $attributes (optional)
     *                               Any additional attributes can be included in an associative array with
     *                               the key being the name and the value being the value of the attribute.
     *                               Attributes set from this array will override existing attributes
     *                               set by previous params.
     *
     * @throws InvalidArgumentException
     *
     * @return XMLElement
     */
    public static function Label($name = null, XmlElement $child = null, $class = null, $id = null, array $attributes = [])
    {
        General::ensureType(
            [
                'name' => ['var' => $name, 'type' => 'string', 'optional' => true],
                'class' => ['var' => $class, 'type' => 'string', 'optional' => true],
                'id' => ['var' => $id, 'type' => 'string', 'optional' => true],
            ]
        );

        return WidgetFactory::build('Label', $name, $child, $class, $id, $attributes)->toXmlElement();
    }

    /**
     * Generates a XmlElement representation of `<input>`.
     *
     * @param string $name
     *                           The name attribute of the resulting `<input>`
     * @param string $value      (optional)
     *                           The value attribute of the resulting `<input>`
     * @param string $type
     *                           The type attribute for this `<input>`, defaults to "text"
     * @param array  $attributes (optional)
     *                           Any additional attributes can be included in an associative array with
     *                           the key being the name and the value being the value of the attribute.
     *                           Attributes set from this array will override existing attributes
     *                           set by previous params.
     *
     * @throws InvalidArgumentException
     *
     * @return XMLElement
     */
    public static function Input(string $name, $value = null, string $type = 'text', array $attributes = []): XmlElement
    {
        General::ensureType(
            [
                'name' => ['var' => $name, 'type' => 'string'],
                'value' => ['var' => $value, 'type' => ['string', 'int', 'double', 'float'], 'optional' => true],
                'type' => ['var' => $type, 'type' => 'string', 'optional' => true],
            ]
        );

        return WidgetFactory::build('Input', $name, $value, $type, $attributes)->toXmlElement();
    }

    /**
     * Generates a XmlElement representation of a `<input type='checkbox'>`. This also
     * includes the actual label of the Checkbox and any help text if required. Note that
     * this includes two input fields, one is the hidden 'no' value and the other
     * is the actual checkbox ('yes' value). This is provided so if the checkbox is
     * not checked, 'no' is still sent in the form request for this `$name`.
     *
     * @since Symphony 2.5.2
     *
     * @param string     $name
     *                                The name attribute of the resulting checkbox
     * @param string     $value
     *                                The value attribute of the resulting checkbox
     * @param string     $description
     *                                This will be localisable and displayed after the checkbox when
     *                                generated
     * @param XmlElement $wrapper
     *                                Passed by reference, if this is provided the elements will be automatically
     *                                added to the wrapper, otherwise they will just be returned
     * @param string     $help        (optional)
     *                                A help message to show below the checkbox
     *
     * @throws InvalidArgumentException
     *
     * @return XMLElement
     *                    The markup for the label and the checkbox
     */
    public static function Checkbox(string $name, $value, $description = null, XmlElement &$wrapper = null, $help = null, array $attributes = []): XmlElement
    {
        General::ensureType([
            'name' => ['var' => $name, 'type' => 'string'],
            'value' => ['var' => $value, 'type' => 'string', 'optional' => true],
            'description' => ['var' => $description, 'type' => 'string'],
            'help' => ['var' => $help, 'type' => 'string', 'optional' => true],
        ]);

        return WidgetFactory::build('Checkbox', $name, $value, $description, $wrapper, $help, $attributes)->toXmlElement();
    }

    /**
     * Generates a XmlElement representation of `<textarea>`.
     *
     * @param string $name
     *                           The name of the resulting `<textarea>`
     * @param int    $rows       (optional)
     *                           The height of the `<textarea>`, using the rows attribute. Defaults to 15
     * @param int    $cols       (optional)
     *                           The width of the `<textarea>`, using the cols attribute. Defaults to 50.
     * @param string $value      (optional)
     *                           The content to be displayed inside the `<textarea>`
     * @param array  $attributes (optional)
     *                           Any additional attributes can be included in an associative array with
     *                           the key being the name and the value being the value of the attribute.
     *                           Attributes set from this array will override existing attributes
     *                           set by previous params.
     *
     * @throws InvalidArgumentException
     *
     * @return XMLElement
     */
    public static function Textarea(string $name, int $rows = 15, int $cols = 50, $value = null, array $attributes = []): XmlElement
    {
        General::ensureType(
            [
                'name' => ['var' => $name, 'type' => 'string'],
                'rows' => ['var' => $rows, 'type' => 'int'],
                'cols' => ['var' => $cols, 'type' => 'int'],
                'value' => ['var' => $value, 'type' => ['string', 'int', 'double', 'float'], 'optional' => true],
            ]
        );

        return WidgetFactory::build('Textarea', $name, $rows, $cols, $value, $attributes)->toXmlElement();
    }

    /**
     * Generates a XmlElement representation of `<a>`.
     *
     * @param string $value
     *                           The text of the resulting `<a>`
     * @param string $href
     *                           The href attribute of the resulting `<a>`
     * @param string $title      (optional)
     *                           The title attribute of the resulting `<a>`
     * @param string $class      (optional)
     *                           The class attribute of the resulting `<a>`
     * @param string $id         (optional)
     *                           The id attribute of the resulting `<a>`
     * @param array  $attributes (optional)
     *                           Any additional attributes can be included in an associative array with
     *                           the key being the name and the value being the value of the attribute.
     *                           Attributes set from this array will override existing attributes
     *                           set by previous params.
     *
     * @throws InvalidArgumentException
     *
     * @return XMLElement
     */
    public static function Anchor($value, string $href, ?string $title = null, ?string $class = null, ?string $id = null, array $attributes = []): XmlElement
    {
        General::ensureType(
            [
                'value' => ['var' => $value, 'type' => ['string', 'int', 'double', 'float']],
                'href' => ['var' => $href, 'type' => 'string'],
                'title' => ['var' => $title, 'type' => 'string', 'optional' => true],
                'class' => ['var' => $class, 'type' => 'string', 'optional' => true],
                'id' => ['var' => $id, 'type' => 'string', 'optional' => true],
            ]
        );

        return WidgetFactory::build('Anchor', $value, $href, $title, $class, $id, $attributes)->toXmlElement();
    }

    /**
     * Generates a XmlElement representation of `<form>`.
     *
     * @param string $action
     *                           The text of the resulting `<form>`
     * @param string $method
     *                           The method attribute of the resulting `<form>`. Defaults to "post".
     * @param string $class      (optional)
     *                           The class attribute of the resulting `<form>`
     * @param string $id         (optional)
     *                           The id attribute of the resulting `<form>`
     * @param array  $attributes (optional)
     *                           Any additional attributes can be included in an associative array with
     *                           the key being the name and the value being the value of the attribute.
     *                           Attributes set from this array will override existing attributes
     *                           set by previous params.
     *
     * @throws InvalidArgumentException
     *
     * @return XMLElement
     */
    public static function Form(?string $action = null, ?string $method = Widgets\Form::METHOD_POST, ?string $class = null, ?string $id = null, array $attributes = []): XmlElement
    {
        General::ensureType(
            [
                'action' => ['var' => $action, 'type' => 'string', 'optional' => true],
                'method' => ['var' => $method, 'type' => 'string'],
                'class' => ['var' => $class, 'type' => 'string', 'optional' => true],
                'id' => ['var' => $id, 'type' => 'string', 'optional' => true],
            ]
        );

        return WidgetFactory::build('Form', $action, $method, $class, $id, $attributes)->toXmlElement();
    }

    /**
     * Generates a XmlElement representation of `<table>`
     * This is a simple way to create generic Symphony table wrapper.
     *
     * @param XmlElement $header
     *                               An XmlElement containing the `<thead>`. See self::TableHead
     * @param XmlElement $footer
     *                               An XmlElement containing the `<tfoot>`
     * @param XmlElement $body
     *                               An XmlElement containing the `<tbody>`. See self::TableBody
     * @param string     $class      (optional)
     *                               The class attribute of the resulting `<table>`
     * @param string     $id         (optional)
     *                               The id attribute of the resulting `<table>`
     * @param array      $attributes (optional)
     *                               Any additional attributes can be included in an associative array with
     *                               the key being the name and the value being the value of the attribute.
     *                               Attributes set from this array will override existing attributes
     *                               set by previous params.
     *
     * @throws InvalidArgumentException
     *
     * @return XMLElement
     */
    public static function Table(?object $header = null, ?object $footer = null, ?object $body = null, $class = null, $id = null, array $attributes = []): XmlElement
    {
        General::ensureType(array(
            'class' => array('var' => $class, 'type' => 'string', 'optional' => true),
            'id' => array('var' => $id, 'type' => 'string', 'optional' => true),
        ));

        return WidgetFactory::build('Table', $header, $footer, $body, $class, $id, $attributes)->toXmlElement();
    }

    /**
     * Generates a XmlElement representation of `<thead>` from an array
     * containing column names and any other attributes.
     *
     * @param array $columns
     *                       An array of column arrays, where the first item is the name of the
     *                       column, the second is the scope attribute, and the third is an array
     *                       of possible attributes.
     *                       `
     *                       array(
     *                       array('Column Name', 'scope', array('class'=>'th-class'))
     *                       )
     *                       `
     *
     * @return XMLElement
     */
    public static function TableHead(array $columns = [], array $attributes = [])
    {
        return WidgetFactory::build('Table\\Head', $columns, $attributes)->toXmlElement();
    }

    /**
     * Generates a XmlElement representation of `<tbody>` from an array
     * containing `<tr>` XMLElements.
     *
     * @see toolkit.Widget#TableRow()
     *
     * @param array  $rows
     *                           An array of XMLElements of `<tr>`'s
     * @param string $class      (optional)
     *                           The class attribute of the resulting `<tbody>`
     * @param string $id         (optional)
     *                           The id attribute of the resulting `<tbody>`
     * @param array  $attributes (optional)
     *                           Any additional attributes can be included in an associative array with
     *                           the key being the name and the value being the value of the attribute.
     *                           Attributes set from this array will override existing attributes
     *                           set by previous params.
     *
     * @throws InvalidArgumentException
     *
     * @return XMLElement
     */
    public static function TableBody(array $rows, ?string $class = null, ?string $id = null, array $attributes = []): XmlElement
    {
        General::ensureType(array(
            'class' => array('var' => $class, 'type' => 'string', 'optional' => true),
            'id' => array('var' => $id, 'type' => 'string', 'optional' => true),
        ));

        return WidgetFactory::build('Table\\Body', $rows, $class, $id, $attributes)->toXmlElement();
    }

    /**
     * Generates a XmlElement representation of `<tr>` from an array
     * containing column names and any other attributes.
     *
     * @param array  $cells
     *                           An array of XMLElements of `<td>`'s. See self::TableData
     * @param string $class      (optional)
     *                           The class attribute of the resulting `<tr>`
     * @param string $id         (optional)
     *                           The id attribute of the resulting `<tr>`
     * @param int    $rowspan    (optional)
     *                           The rowspan attribute of the resulting `<tr>`
     * @param array  $attributes (optional)
     *                           Any additional attributes can be included in an associative array with
     *                           the key being the name and the value being the value of the attribute.
     *                           Attributes set from this array will override existing attributes
     *                           set by previous params.
     *
     * @throws InvalidArgumentException
     *
     * @return XMLElement
     */
    public static function TableRow(array $cells, ?string $class = null, ?string $id = null, ?int $rowspan = null, array $attributes = []): XmlElement
    {
        General::ensureType(array(
            'class' => array('var' => $class, 'type' => 'string', 'optional' => true),
            'id' => array('var' => $id, 'type' => 'string', 'optional' => true),
            'rowspan' => array('var' => $rowspan, 'type' => 'int', 'optional' => true),
        ));

        return WidgetFactory::build('Table\\Row', $cells, $class, $id, $rowspan, $attributes)->toXmlElement();
    }

    /**
     * Generates a XmlElement representation of a `<td>`.
     *
     * @param XMLElement|string $value
     *                                      Either an XmlElement object, or a string for the value of the
     *                                      resulting `<td>`
     * @param string            $class      (optional)
     *                                      The class attribute of the resulting `<td>`
     * @param string            $id         (optional)
     *                                      The id attribute of the resulting `<td>`
     * @param int               $colspan    (optional)
     *                                      The colspan attribute of the resulting `<td>`
     * @param array             $attributes (optional)
     *                                      Any additional attributes can be included in an associative array with
     *                                      the key being the name and the value being the value of the attribute.
     *                                      Attributes set from this array will override existing attributes
     *                                      set by previous params.
     *
     * @throws InvalidArgumentException
     *
     * @return XMLElement
     */
    public static function TableData($value, ?string $class = null, ?string $id = null, ?int $colspan = null, array $attributes = []): XmlElement
    {
        General::ensureType(array(
            'class' => array('var' => $class, 'type' => 'string', 'optional' => true),
            'id' => array('var' => $id, 'type' => 'string', 'optional' => true),
            'colspan' => array('var' => $colspan, 'type' => 'int', 'optional' => true),
        ));

        return WidgetFactory::build('Table\\Data', $value, $class, $id, $colspan, $attributes)->toXmlElement();
    }

    /**
     * Generates a XmlElement representation of a `<time>`.
     *
     * @since Symphony 2.3
     *
     * @param string $string
     *                                A string containing date and time, defaults to the current date and time
     * @param string $format          (optional)
     *                                A valid PHP date format, defaults to `__SYM_TIME_FORMAT__`
     * @param bool   $isPublishedDate (optional)
     *                                A flag to make the given date a publish date
     *
     * @return XMLElement
     */
    public static function Time(string $string = 'now', string $format = __SYM_TIME_FORMAT__, bool $isPublishedDate = false, array $attributes = []): XmlElement
    {
        return WidgetFactory::build('Time', $string, $format, $isPublishedDate, $attributes)->toXmlElement();
    }

    /**
     * Generates a XmlElement representation of a `<select>`. This uses
     * the private function `selectBuildOption()` to build XMLElements of
     * options given the `$options` array.
     *
     * @see toolkit.Widget::selectBuildOption()
     *
     * @param string $name
     *                           The name attribute of the resulting `<select>`
     * @param array  $options    (optional)
     *                           An array containing the data for each `<option>` for this
     *                           `<select>`. If the array is associative, it is assumed that
     *                           `<optgroup>` are to be created, otherwise it's an array of the
     *                           containing the option data. If no options are provided an empty
     *                           `<select>` XmlElement is returned.
     *                           `
     *                           array(
     *                           array($value, $selected, $desc, $class, $id, $attr)
     *                           )
     *                           array(
     *                           array('label' => 'Optgroup', 'data-label' => 'optgroup', 'options' = array(
     *                           array($value, $selected, $desc, $class, $id, $attr)
     *                           )
     *                           )
     *                           `
     * @param array  $attributes (optional)
     *                           Any additional attributes can be included in an associative array with
     *                           the key being the name and the value being the value of the attribute.
     *                           Attributes set from this array will override existing attributes
     *                           set by previous params.
     *
     * @throws InvalidArgumentException
     *
     * @return XMLElement
     */
    public static function Select(string $name, ?array $options = [], array $attributes = []): XmlElement
    {
        General::ensureType(array(
            'name' => array('var' => $name, 'type' => 'string'),
        ));

        return WidgetFactory::build('Select', $name, $options, $attributes)->toXmlElement();
    }

    /**
     * Generates a XmlElement representation of a `<fieldset>` containing
     * the "With selected…" menu. This uses the private function `selectBuildOption()`
     * to build `XMLElement`'s of options given the `$options` array.
     *
     * @since Symphony 2.3
     * @see toolkit.Widget::selectBuildOption()
     *
     * @param array $options (optional)
     *                       An array containing the data for each `<option>` for this
     *                       `<select>`. If the array is associative, it is assumed that
     *                       `<optgroup>` are to be created, otherwise it's an array of the
     *                       containing the option data. If no options are provided an empty
     *                       `<select>` XmlElement is returned.
     *                       `
     *                       array(
     *                       array($value, $selected, $desc, $class, $id, $attr)
     *                       )
     *                       array(
     *                       array('label' => 'Optgroup', 'options' = array(
     *                       array($value, $selected, $desc, $class, $id, $attr)
     *                       )
     *                       )
     *                       `
     *
     * @throws InvalidArgumentException
     *
     * @return XMLElement
     */
    public static function Apply(?array $options = [], array $attributes = []): XmlElement
    {
        return WidgetFactory::build('Apply', $options, $attributes)->toXmlElement();
    }

    /**
     * Will wrap a `<div>` around a desired element to trigger the default
     * Symphony error styling.
     *
     * @since Symphony 2.3
     *
     * @param XmlElement $element
     *                            The element that should be wrapped with an error
     * @param string     $message
     *                            The text for this error. This will be appended after the $element,
     *                            but inside the wrapping `<div>`
     *
     * @throws InvalidArgumentException
     *
     * @return XMLElement
     */
    public static function Error(XmlElement $element, string $message, array $attributes = []): XmlElement
    {
        General::ensureType(array(
            'message' => array('var' => $message, 'type' => 'string'),
        ));

        return WidgetFactory::build('Error', $element, $message, $attributes)->toXmlElement();
    }

    /**
     * Generates a XmlElement representation of a Symphony drawer widget.
     * A widget is identified by it's `$label`, and it's contents is defined
     * by the `XMLElement`, `$content`.
     *
     * @since Symphony 2.3
     *
     * @param string     $id
     *                                  The id attribute for this drawer
     * @param string     $label
     *                                  A name for this drawer
     * @param XmlElement $content
     *                                  An XmlElement containing the HTML that should be contained inside
     *                                  the drawer
     * @param string     $default_state
     *                                  This parameter defines whether the drawer will be open or closed by
     *                                  default. It defaults to closed.
     * @param string     $context
     * @param array      $attributes    (optional)
     *                                  Any additional attributes can be included in an associative array with
     *                                  the key being the name and the value being the value of the attribute.
     *                                  Attributes set from this array will override existing attributes
     *                                  set by previous params, except the `id` attribute.
     *
     * @return XMLElement
     */
    public static function Drawer(string $id, string $label, XmlElement $content, $defaultState = Drawer::STATE_CLOSED, ?string $context = null, array $attributes = []): XmlElement
    {
        return WidgetFactory::build('Drawer', $id, $label, $content, $defaultState, $context, $attributes)->toXmlElement();
    }

    /**
     * Generates a XmlElement representation of a Symphony calendar.
     *
     * @since Symphony 2.6
     *
     * @param bool $time
     *                   Wheather or not to display the time, defaults to true
     *
     * @return XMLElement
     */
    public static function Calendar(bool $isTime = true, array $attributes = []): XmlElement
    {
        return WidgetFactory::build('Calendar', $isTime, $attributes)->toXmlElement();
    }
}
