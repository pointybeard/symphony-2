<?php

namespace Symphony\Symphony;

/**
 * The `XsltProcess` class is responsible for taking a chunk of XML
 * and applying an XSLT stylesheet to it. Custom error handlers are
 * used to capture any errors that occurred during this process, and
 * are exposed to the `ExceptionHandler`'s for display to the user.
 */
class XsltProcess extends AbstractXSLTProcessor
{
    /**
     * Checks if there is an available `XSLTProcessor`.
     *
     * @return bool
     *              true if there is an existing `XsltProcessor` class, false otherwise
     */
    public static function isXSLTProcessorAvailable(): bool
    {
        return class_exists('XSLTProcessor');
    }

    /**
     * This function will take a given XML file, a stylesheet and apply
     * the transformation. Any errors will call the error function to log
     * them into the `$_errors` array.
     *
     * @see toolkit.XSLTProcess#appendError()
     * @see toolkit.XSLTProcess#__process()
     *
     * @param string $xml
     *                                  The XML for the transformation to be applied to
     * @param string $xsl
     *                                  The XSL for the transformation
     * @param array  $parameters
     *                                  An array of available parameters the XSL will have access to
     * @param array  $registerFunctions
     *                                  An array of available PHP functions that the XSL can use
     *
     * @return string|bool
     *                     The string of the resulting transform, or false if there was an error
     */
    public function process(?string $xml = null, ?string $xsl = null, array $parameters = [], array $registerFunctions = [])
    {
        parent::process($xml, $xsl, $parameters, $registerFunctions);

        // dont let process continue if no xsl functionality exists
        if (false == self::isXSLTProcessorAvailable()) {
            return false;
        }

        $XSLProc = new \XSLTProcessor();

        if (false == empty($registerFunctions)) {
            $XSLProc->registerPHPFunctions($registerFunctions);
        }

        $result = @$this->__process(
            $XSLProc,
            $this->xml(),
            $this->xsl(),
            $parameters
        );

        unset($XSLProc);

        return $result;
    }

    /**
     * Uses `DomDocument` to transform the document. Any errors that
     * occur are trapped by custom error handlers, `trapXMLError` or
     * `trapXSLError`.
     *
     * @param XsltProcessor $XSLProc
     *                                  An instance of `XsltProcessor`
     * @param string        $xml
     *                                  The XML for the transformation to be applied to
     * @param string        $xsl
     *                                  The XSL for the transformation
     * @param array         $parameters
     *                                  An array of available parameters the XSL will have access to
     *
     * @return string
     */
    private function __process(\XsltProcessor $XSLProc, $xml, $xsl, array $parameters = [])
    {
        // Create instances of the DomDocument class
        $xmlDoc = new \DomDocument();
        $xslDoc = new \DomDocument();

        // Set up error handling
        if (function_exists('ini_set')) {
            $ehOLD = ini_set('html_errors', false);
        }

        // Load the xml document
        set_error_handler(array($this, 'trapXMLError'));
        // Prevent remote entities from being loaded, RE: #1939
        $elOLD = libxml_disable_entity_loader(true);
        // Remove null bytes from XML
        $xml = str_replace(chr(0), '', $xml);
        $xmlDoc->loadXML($xml, LIBXML_NONET | LIBXML_DTDLOAD | LIBXML_DTDATTR | defined('LIBXML_COMPACT') ? LIBXML_COMPACT : 0);
        libxml_disable_entity_loader($elOLD);

        // Must restore the error handler to avoid problems
        restore_error_handler();

        // Load the xsl document
        set_error_handler(array($this, 'trapXSLError'));
        // Ensure that the XSLT can be loaded with `false`. RE: #1939
        // Note that `true` will cause `<xsl:import />` to fail.
        $elOLD = libxml_disable_entity_loader(false);
        $xslDoc->loadXML($xsl, LIBXML_NONET | LIBXML_DTDLOAD | LIBXML_DTDATTR | defined('LIBXML_COMPACT') ? LIBXML_COMPACT : 0);
        libxml_disable_entity_loader($elOLD);

        // Load the xsl template
        $XSLProc->importStyleSheet($xslDoc);

        // Set parameters when defined
        if (false == empty($parameters)) {
            General::flattenArray($parameters);

            $XSLProc->setParameter('', $parameters);
        }

        // Must restore the error handler to avoid problems
        restore_error_handler();

        // Start the transformation
        set_error_handler(array($this, 'trapXMLError'));
        $processed = $XSLProc->transformToXML($xmlDoc);

        // Restore error handling
        if (function_exists('ini_set') && isset($ehOLD)) {
            ini_set('html_errors', $ehOLD);
        }

        // Must restore the error handler to avoid problems
        restore_error_handler();

        return $processed;
    }

    /**
     * That validate function takes an XSD to valid against `$this->xml`
     * returning boolean. Optionally, a second parameter `$xml` can be
     * passed that will be used instead of `$this->xml`.
     *
     * @since Symphony 2.3
     *
     * @param string $xsd
     *                    The XSD to validate `$this->xml` against
     * @param string $xml (optional)
     *                    If provided, this function will use this `$xml` instead of
     *                    `$this->xml`
     *
     * @return bool
     *              Returns true if the `$xml` validates against `$xsd`, false otherwise.
     *              If false is returned, the errors can be obtained with `XSLTProcess->getErrors()`
     */
    public function validate(string $xsd, ?string $xml = null): bool
    {
        $xml = $xml ?? $this->xml();

        if (null == $xsd || null == $xml) {
            return false;
        }

        // Create instances of the DomDocument class
        $xmlDoc = new \DomDocument();

        // Set up error handling
        if (true == function_exists('ini_set')) {
            $ehOLD = ini_set('html_errors', false);
        }

        // Load the xml document
        set_error_handler(array($this, 'trapXMLError'));
        $elOLD = libxml_disable_entity_loader(true);
        $xmlDoc->loadXML($xml, LIBXML_NONET | LIBXML_DTDLOAD | LIBXML_DTDATTR | defined('LIBXML_COMPACT') ? LIBXML_COMPACT : 0);
        libxml_disable_entity_loader($elOLD);

        // Must restore the error handler to avoid problems
        restore_error_handler();

        // Validate the XML against the XSD
        set_error_handler(array($this, 'trapXSDError'));
        $result = $xmlDoc->schemaValidateSource($xsd);

        // Restore error handling
        if (function_exists('ini_set') && isset($ehOLD)) {
            ini_set('html_errors', $ehOLD);
        }

        // Must restore the error handler to avoid problems
        restore_error_handler();

        return $result;
    }

    /**
     * A custom error handler especially for XML errors.
     *
     * @see http://au.php.net/manual/en/function.set-error-handler.php
     *
     * @param int $errno
     * @param int $errstr
     * @param int $errfile
     * @param int $errline
     */
    public function trapXMLError($errno, $errstr, $errfile, $errline)
    {
        $this->appendError($errno, str_replace('DOMDocument::', null, $errstr), $errfile, $errline, self::TYPE_XML);
    }

    /**
     * A custom error handler especially for XSL errors.
     *
     * @see http://au.php.net/manual/en/function.set-error-handler.php
     *
     * @param int $errno
     * @param int $errstr
     * @param int $errfile
     * @param int $errline
     */
    public function trapXSLError($errno, $errstr, $errfile, $errline)
    {
        $this->appendError($errno, str_replace('DOMDocument::', null, $errstr), $errfile, $errline, self::TYPE_XSL);
    }

    /**
     * A custom error handler especially for XSD errors.
     *
     * @since Symphony 2.3
     * @see http://au.php.net/manual/en/function.set-error-handler.php
     *
     * @param int $errno
     * @param int $errstr
     * @param int $errfile
     * @param int $errline
     */
    public function trapXSDError($errno, $errstr, $errfile, $errline)
    {
        $this->appendError($errno, str_replace('DOMDocument::', null, $errstr), $errfile, $errline, self::TYPE_XSD);
    }
}
