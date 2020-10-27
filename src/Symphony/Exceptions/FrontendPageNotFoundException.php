<?php

namespace Symphony\Symphony\Exceptions;

/**
 * `FrontendPageNotFoundException` extends a default Exception, it adds nothing
 * but allows a different Handler to be used to render the Exception.
 *
 * @see core.FrontendPageNotFoundExceptionHandler
 */
class FrontendPageNotFoundException extends SymphonyException
{
    /**
     * The constructor for `FrontendPageNotFoundException` sets the default
     * error message and code for Logging purposes.
     */
    public function __construct(\Throwable $previous = null)
    {
        $pagename = getCurrentPage();

        if (empty($pagename)) {
            $message = __('The page you requested does not exist.');
        } else {
            $message = __('The page you requested, %s, does not exist.', array('<code>'.$pagename.'</code>'));
        }

        $code = E_USER_NOTICE;

        parent::__construct($message, $code, $previous);
    }
}
