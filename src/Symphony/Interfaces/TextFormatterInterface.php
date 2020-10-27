<?php

namespace Symphony\Symphony\Interfaces;

/**
 * The Singleton interface contains one function, `instance()`,
 * the will return an instance of an Object that implements this
 * interface.
 */
interface TextFormatterInterface
{
    /**
     * The about method allows a text formatter to provide
     * information about itself as an associative array. eg.
     * `array(
     *      'name' => 'Name of Formatter',
     *      'version' => '1.8',
     *      'release-date' => 'YYYY-MM-DD',
     *      'author' => array(
     *          'name' => 'Author Name',
     *          'website' => 'Author Website',
     *          'email' => 'Author Email'
     *      ),
     *      'description' => 'A description about this formatter'
     * )`.
     *
     * @return array
     *               An associative array describing the text formatter
     */
    public function about();

    /**
     * Given an input, apply the formatter and return the result.
     *
     * @param string $string
     *
     * @return string
     */
    public function run($string);
}
