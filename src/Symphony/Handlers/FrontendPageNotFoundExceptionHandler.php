<?php

namespace Symphony\Symphony\Handlers;

use Symphony\Symphony;
use Symphony\Symphony\Exceptions;
use Symphony\Symphony\Managers;

/**
 * The `FrontendPageNotFoundExceptionHandler` attempts to find a Symphony
 * page that has been given the '404' page type to render the SymphonyErrorPage
 * error, instead of using the Symphony default.
 */
class FrontendPageNotFoundExceptionHandler extends SymphonyErrorPageExceptionHandler
{
    /**
     * The render function will take a `FrontendPageNotFoundException` Exception and
     * output a HTML page. This function first checks to see if their is a page in Symphony
     * that has been given the '404' page type, otherwise it will just use the default
     * Symphony error page template to output the exception.
     *
     * @param \Throwable $e
     *                      The \Throwable object
     *
     * @throws FrontendPageNotFoundException
     * @throws SymphonyErrorPage
     *
     * @return string
     *                An HTML string
     */
    public static function render(\Throwable $e): string
    {
        $page = Managers\PageManager::fetchPageByType('404');
        $previousException = \Frontend::instance()->getException();

        // No 404 detected, throw default Symphony error page
        if (null === $page['id']) {
            parent::render(new Exceptions\SymphonyErrorPageException(
                $e->getMessage(),
                __('Page Not Found'),
                'generic',
                [],
                Symphony\AbstractPage::HTTP_STATUS_NOT_FOUND
            ));

        // Recursive 404
        } elseif (isset($previousException)) {
            parent::render(new Exceptions\SymphonyErrorPageException(
                __('This error occurred whilst attempting to resolve the 404 page for the original request.').' '.$e->getMessage(),
                __('Page Not Found'),
                'generic',
                [],
                Symphony\AbstractPage::HTTP_STATUS_NOT_FOUND
            ));

        // Handle 404 page
        } else {
            $url = '/'.Managers\PageManager::resolvePagePath($page['id']).'/';

            \Frontend::instance()->setException($e);
            echo \Frontend::instance()->display($url);

            exit(1);
        }
    }
}
