<?php

//declare(strict_types=1);

namespace Symphony\Symphony\Handlers;

use Symphony\Symphony;
use Symphony\Symphony\Exceptions;

/**
 * The `SymphonyErrorPageHandler` extends the `GenericExceptionHandler`
 * to allow the template for the exception to be provided from the `TEMPLATES`
 * directory.
 */
class SymphonyErrorPageExceptionHandler extends GenericExceptionHandler
{
    /**
     * The render function will take a `SymphonyErrorPage` exception and
     * output a HTML page. This function first checks to see if their is a custom
     * template for this exception otherwise it reverts to using the default
     * `usererror.generic.php`.
     *
     * @param Throwable $e
     *                     The Throwable object
     *
     * @return string
     *                An HTML string
     */
    public static function render(\Throwable $e): string
    {
        // Validate the type, resolve to a 404 if not valid
        if (!static::isValidThrowable($e)) {
            $e = new Exceptions\FrontendPageNotFoundException;
        }

        if (false === $e->getTemplate()) {
            Symphony\AbstractPage::renderStatusCode($e->getHttpStatusCode());

            if (isset($e->getAdditional()->header)) {
                header($e->getAdditional()->header);
            }

            echo '<h1>Symphony Fatal Error</h1><p>'.$e->getMessage().'</p>';
            exit;
        }

        include $e->getTemplate();
    }
}
