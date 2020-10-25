<?php

//declare(strict_types=1);

namespace Symphony\Symphony;

/**
 * XSLTPage extends the Page class to provide an object representation
 * of a Page that will be generated using XSLT.
 */
abstract class AbstractXsltPage extends AbstractPage
{
    /**
     * An instance of the XsltProcess class.
     *
     * @var XsltProcess
     */
    public $Proc;

    /**
     * The XML to be transformed.
     *
     * @since Symphony 2.4 this variable may be a string or an XMLElement
     *
     * @var string|XMLElement
     */
    protected $_xml;

    /**
     * The XSL to apply to the `$this->_xml`.
     *
     * @var string
     */
    protected $_xsl;

    /**
     * An array of all the parameters to be made available during the XSLT
     * transform.
     *
     * @var array
     */
    protected $_param = [];

    /**
     * An array of the PHP functions to be made available during the XSLT
     * transform.
     *
     * @var array
     */
    protected $_registered_php_functions = [];

    /**
     * The constructor for the `XSLTPage` ensures that an `XSLTProcessor`
     * is available, and then sets an instance of it to `$this->Proc`, otherwise
     * it will throw a `SymphonyErrorPage` exception.
     */
    public function __construct(AbstractXSLTProcessor $proc = null)
    {
        if (!($proc instanceof AbstractXSLTProcessor)) {
            // See what the config says. If it's not set, then use the
            // default libxsl processor (xslt 1.0)
            $procClass = \Symphony::Configuration()->get('processor', 'xslt') ?? '\\XsltProcess';

            $proc = new $procClass();
        }

        if (!$procClass::isXSLTProcessorAvailable()) {
            \Symphony::Engine()->throwCustomError(__('No suitable XSLT processor was found.'));
        }

        $this->Proc = $proc;
    }

    /**
     * Setter for `$this->_xml`, can optionally load the XML from a file.
     *
     * @param string|XMLElement $xml
     *                                  The XML for this XSLT page
     * @param bool              $isFile
     *                                  If set to true, the XML will be loaded from a file. It is false by default
     */
    public function setXML($xml, $isFile = false)
    {
        $this->_xml = ($isFile ? file_get_contents($xml) : $xml);
    }

    /**
     * Accessor for the XML of this page.
     *
     * @return string|XMLElement
     */
    public function getXML()
    {
        return $this->_xml;
    }

    /**
     * Setter for `$this->_xsl`, can optionally load the XSLT from a file.
     *
     * @param string $xsl
     *                       The XSLT for this XSLT page
     * @param bool   $isFile
     *                       If set to true, the XSLT will be loaded from a file. It is false by default
     */
    public function setXSL($xsl, $isFile = false)
    {
        $this->_xsl = ($isFile ? file_get_contents($xsl) : $xsl);
    }

    /**
     * Accessor for the XSL of this page.
     *
     * @return string
     */
    public function getXSL()
    {
        return $this->_xsl;
    }

    /**
     * Sets the parameters that will output with the resulting page
     * and be accessible in the XSLT. This function translates all ' into
     * `&apos;`, with the tradeoff being that a <xsl:value-of select='$param' />
     * that has a ' will output `&apos;` but the benefit that ' and " can be
     * in the params.
     *
     * @see http://www.php.net/manual/en/xsltprocessor.setparameter.php#81077
     *
     * @param array $param
     *                     An associative array of params for this page
     */
    public function setRuntimeParam($param)
    {
        $this->_param = str_replace("'", '&apos;', $param);
    }

    /**
     * Returns an iterator of errors from the `XsltProcess`. Use this function
     * inside a loop to get all the errors that occurring when transforming
     * `$this->_xml` with `$this->_xsl`.
     *
     * @return array
     *               An associative array containing the errors details from the
     *               `XsltProcessor`
     */
    public function getError()
    {
        return $this->Proc->getError();
    }

    /**
     * Allows the registration of PHP functions to be used on the Frontend
     * by passing the function name or an array of function names.
     *
     * @param mixed $function
     *                        Either an array of function names, or just the function name as a
     *                        string
     */
    public function registerPHPFunction($function)
    {
        if (is_array($function)) {
            $this->_registered_php_functions = array_unique(
                array_merge($this->_registered_php_functions, $function)
            );
        } else {
            $this->_registered_php_functions[] = $function;
        }
    }

    /**
     * The generate function calls on the `XsltProcess` to transform the
     * XML with the given XSLT passing any parameters or functions
     * If no errors occur, the parent generate function is called to add
     * the page headers and a string containing the transformed result
     * is result.
     *
     * @param null $page
     *
     * @return string
     */
    public function generate($page = null)
    {
        $result = $this->Proc->process($this->_xml, $this->_xsl, $this->_param, $this->_registered_php_functions);

        if ($this->Proc->isErrors()) {
            $this->setHttpStatus(AbstractPage::HTTP_STATUS_ERROR);

            return false;
        }

        parent::generate($page);

        return $result;
    }
}