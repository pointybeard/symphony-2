<?php

namespace Symphony\Symphony\Exceptions;

use Symphony\Symphony;

/**
 * `SymphonyErrorPage` extends the default `Exception` class. All
 * of these exceptions will halt execution immediately and return the
 * exception as a HTML page. By default the HTML template is `Generic.php`
 * from the `ERROR_PAGES` directory.
 */
class SymphonyErrorPageException extends SymphonyException
{
    /**
     * A heading for the error page, this will be prepended to
     * "Symphony Fatal Error".
     *
     * @return string
     */
    private $heading;

    /**
     * A string for the error page template to use, defaults to 'Generic'. This
     * can be the name of any template file in the `ERROR_PAGES` directory.
     *
     * @var string
     */
    private $template = 'Generic';

    /**
     * If the message as provided as an `XMLElement`, it will be saved to
     * this parameter.
     *
     * @var XMLElement
     */
    private $messageObject = null;

    /**
     * An object of an additional information for this error page. Note that
     * this is provided as an array and then typecast to an object.
     *
     * @var StdClass
     */
    private $additional = null;

    /**
     * A simple container for the response status code.
     * Full value is setted usign `$Page->setHttpStatus()`
     * in the template.
     */
    private $status = Symphony\AbstractPage::HTTP_STATUS_ERROR;

    /**
     * Constructor for SymphonyErrorPage sets it's class variables.
     *
     * @param string|XMLElement $message
     *                                      A description for this error, which can be provided as a string
     *                                      or as an XMLElement
     * @param string            $heading
     *                                      A heading for the error page, by default this is "Symphony Fatal Error"
     * @param string            $template
     *                                      A string for the error page template to use, defaults to 'Generic'. This
     *                                      can be the name of any template file in the `TEMPLATES` directory.
     *                                      A template using the naming convention of `tpl.*.php`.
     * @param array             $additional
     *                                      Allows custom information to be passed to the Symphony Error Page
     *                                      that the template may want to expose, such as custom Headers etc
     * @param int               $status
     *                                      Properly sets the HTTP status code for the response. Defaults to
     *                                      `Symphony\AbstractPage::HTTP_STATUS_ERROR`
     */
    public function __construct($message, $heading = 'Symphony Fatal Error', $template = 'Generic', array $additional = [], $status = Symphony\AbstractPage::HTTP_STATUS_ERROR)
    {
        if ($message instanceof \XMLElement) {
            $this->messageObject = $message;
            $message = $this->messageObject->generate();
        }

        parent::__construct($message);

        $this->heading = $heading;
        $this->template = $template;
        $this->additional = (object) $additional;
        $this->status = $status;
    }

    /**
     * Accessor for the `$heading` of the error page.
     *
     * @return string
     */
    public function getHeading()
    {
        return $this->heading;
    }

    /**
     * Accessor for `$messageObject`.
     *
     * @return XMLElement
     */
    public function getMessageObject()
    {
        return $this->messageObject;
    }

    /**
     * Accessor for `$additional`.
     *
     * @return StdClass
     */
    public function getAdditional()
    {
        return $this->additional;
    }

    /**
     * Accessor for `$status`.
     *
     * @since Symphony 2.3.2
     *
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->status;
    }

    /**
     * Returns the path to the current template by looking at the
     * `WORKSPACE/template/` directory, then at the `ERROR_PAGES`
     * directory. If the template is not found, `false` is returned.
     *
     * @since Symphony 2.3
     *
     * @return string|false
     *                      String, which is the path to the template if the template is found,
     *                      false otherwise
     */
    public function getTemplate()
    {
        // Keep the "usererror.*.php" format for custom error pages. This
        // preserves backwards compatibility
        if (file_exists($template = sprintf('%s/usererror.%s.php', WORKSPACE.'/template', $this->template))) {
            return $template;
        } elseif (file_exists($template = sprintf('%s/%s.php', ERROR_PAGES, ucfirst(strtolower($this->template))))) {
            return $template;
        }

        return false;
    }

    /**
     * A simple getter to the template name in order to be able
     * to identify which type of exception this is.
     *
     * @since Symphony 2.3.2
     *
     * @return string
     */
    public function getTemplateName()
    {
        return $this->template;
    }
}
