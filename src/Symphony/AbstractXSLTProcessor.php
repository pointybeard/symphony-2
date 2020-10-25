<?php

//declare(strict_types=1);

namespace Symphony\Symphony;

abstract class AbstractXSLTProcessor implements Interfaces\XSLTProcessorInterface
{
    protected const TYPE_XML = 'xml';
    protected const TYPE_XSL = 'xsl';
    protected const TYPE_XSD = 'xsd';

    /**
     * The XML for the transformation to be applied to.
     *
     * @var string
     */
    protected $xml;

    /**
     * The XSL for the transformation.
     *
     * @var string
     */
    protected $xsl;

    /**
     * Any errors that occur during the transformation are stored in this array.
     *
     * @var array
     */
    protected $errors;

    public function xml()
    {
        return $this->xml;
    }

    public function xsl()
    {
        return $this->xsl;
    }

    public function process(?string $xml = null, ?string $xsl = null, array $parameters = [], array $register_functions = [])
    {
        $this->xml = $xml ?? $this->xml();
        $this->xsl = $xsl ?? $this->xsl();

        // Clear out the error iterator
        $this->errors = new \ArrayIterator();
    }

    /**
     * Writes an error to the `$errors` array, which contains the error information
     * and some basic debugging information.
     *
     * @see http://au.php.net/manual/en/function.set-error-handler.php
     *
     * @param mixed  $number
     * @param string $message
     * @param string $file
     * @param string $line
     * @param string $type
     *                        Where the error occurred, can be either 'xml', 'xsl' or `xsd`
     */
    protected function appendError($number, string $message, ?string $file = null, ?string $line = null, $type = null): void
    {
        if (!($this->errors instanceof \ArrayIterator)) {
            $this->errors = new \ArrayIterator();
        }

        $context = null;

        if (self::TYPE_XML == $type || self::TYPE_XSD == $type) {
            $context = $this->xml();
        } elseif (self::TYPE_XSL == $type) {
            $context = $this->xsl();
        }

        $this->errors->append([
            'number' => $number,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'type' => $type,
            'context' => $context,
        ]);
    }

    /**
     * Returns boolean if any errors occurred during the transformation.
     *
     * @see getError
     *
     * @return bool
     */
    public function isErrors(): bool
    {
        return $this->errors instanceof \ArrayIterator && count($this->errors) > 0;
    }

    /**
     * Provides an Iterator interface to return an error from the `$errors`
     * array. Repeat calls to this function to get all errors.
     *
     * @param bool $all
     *                     If true, return all errors instead of one by one. Defaults to false
     * @param bool $rewind
     *                     If rewind is true, resets the internal array pointer to the start of
     *                     the `$errors` array. Defaults to false.
     *
     * @return array
     *               Either an array of error array's or just an error array
     */
    public function getError(bool $all = false, bool $rewind = false): ?array
    {
        if (true == $rewind) {
            $this->errors->rewind();
        }

        if (true == $all) {
            return $this->errors->getArrayCopy();
        }

        if (false == $this->errors->valid()) {
            return null;
        }

        $result = $this->errors->current();
        $this->errors->next();

        // each() was depricated from PHP 7.2.0, however, we need to emulate
        // it since a few things in Symphony will be expecting the same
        // output when calling this method.
        return [$this->errors->key(), $result];
    }
}
