<?php

namespace Symphony\Symphony;

/**
 * The Frontend class is the renderer that is used to display FrontendPage's.
 * A `FrontendPage` is one that is setup in Symphony and it's output is generated
 * by using XML and XSLT.
 */
class Frontend extends Symphony
{

    use Traits\SingletonTrait;

    /**
     * An instance of the FrontendPage class.
     *
     * @var FrontendPage
     */
    private static $page;

    /**
     * The constructor for Frontend calls the parent Symphony constructor.
     *
     * @see core.Symphony#__construct()
     */
    protected function __construct()
    {
        parent::__construct();

        $this->_env = [];
    }

    /**
     * Accessor for `$page`.
     *
     * @return FrontendPage
     */
    public static function Page()
    {
        return self::$page;
    }

    /**
     * Overrides the Symphony `isLoggedIn()` function to allow Authors
     * to become logged into the frontend when `$_REQUEST['auth-token']`
     * is present. This logs an Author in using the loginFromToken function.
     * This function allows the use of 'admin' type pages, where a Frontend
     * page requires that the viewer be a Symphony Author.
     *
     * @see core.Symphony#loginFromToken()
     * @see core.Symphony#isLoggedIn()
     *
     * @return bool
     */
    public static function isLoggedIn()
    {
        if (isset($_REQUEST['auth-token']) && $_REQUEST['auth-token'] && 8 == strlen($_REQUEST['auth-token'])) {
            return self::loginFromToken($_REQUEST['auth-token']);
        }

        return parent::isLoggedIn();
    }

    /**
     * Called by index.php, this function is responsible for rendering the current
     * page on the Frontend. One delegate is fired, `FrontendInitialised`.
     *
     * @uses FrontendInitialised
     *
     * @see boot.getCurrentPage()
     *
     * @param string $page
     *                     The result of getCurrentPage, which returns the `$_GET['symphony-page']`
     *
     * @throws FrontendPageNotFoundException
     * @throws SymphonyErrorPage
     *
     * @return string
     *                The HTML of the page to return
     */
    public function display($page)
    {
        self::$page = new Frontend\Page;

        /*
         * `FrontendInitialised` is fired just after the `$page` variable has been
         * created with an instance of the `FrontendPage` class. This delegate is
         * fired just before the `FrontendPage->generate()`.
         *
         * @delegate FrontendInitialised
         * @param string $context
         *  '/frontend/'
         */
        self::ExtensionManager()->notifyMembers('FrontendInitialised', '/frontend/');

        $output = self::$page->generate($page);

        return $output;
    }
}
