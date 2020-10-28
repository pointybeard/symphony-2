<?php

namespace Symphony\Symphony;

/**
 * The Symphony class is an abstract class that implements the
 * Singleton interface. It provides the glue that forms the Symphony
 * CMS and initialises the toolkit classes. Symphony is extended by
 * the Frontend and Administration classes.
 */
abstract class Symphony implements Interfaces\SingletonInterface
{
    /**
     * An instance of the Profiler class.
     *
     * @var Profiler
     */
    protected static $Profiler = null;

    /**
     * An instance of the `Configuration` class.
     *
     * @var Configuration
     */
    private static $Configuration = null;

    /**
     * An instance of the `Database` class.
     *
     * @var MySQL
     */
    private static $Database = null;

    /**
     * An instance of the `ExtensionManager` class.
     *
     * @var ExtensionManager
     */
    private static $ExtensionManager = null;

    /**
     * An instance of the `Log` class.
     *
     * @var Log
     */
    private static $Log = null;

    /**
     * The current page namespace, used for translations.
     *
     * @since Symphony 2.3
     *
     * @var string
     */
    private static $namespace = false;

    /**
     * An instance of the Cookie class.
     *
     * @var Cookie
     */
    public static $Cookie = null;

    /**
     * An instance of the currently logged in Author.
     *
     * @var Author
     */
    public static $Author = null;

    /**
     * A previous exception that has been fired. Defaults to null.
     *
     * @since Symphony 2.3.2
     *
     * @var Exception
     */
    private static $exception = null;

    /**
     * The Symphony constructor initialises the class variables of Symphony. At present
     * constructor has a couple of responsibilities:
     * - Start a profiler instance
     * - If magic quotes are enabled, clean `$_SERVER`, `$_COOKIE`, `$_GET`, `$_POST` and the `$_REQUEST` arrays.
     * - Initialise the correct Language for the currently logged in Author.
     * - Start the session and adjust the error handling if the user is logged in.
     *
     * The `$_REQUEST` array has been added in 2.7.0
     */
    protected function __construct()
    {
        self::$Profiler = Profiler::instance();

        if (get_magic_quotes_gpc()) {
            General::cleanArray($_SERVER);
            General::cleanArray($_COOKIE);
            General::cleanArray($_GET);
            General::cleanArray($_POST);
            General::cleanArray($_REQUEST);
        }

        // Initialize language management
        Lang::initialize();
        Lang::set(self::$Configuration->get('lang', 'symphony'));

        self::initialiseCookie();

        // If the user is not a logged in Author, turn off the verbose error messages.
        if (!self::isLoggedIn() && null === self::$Author) {
            Handlers\GenericExceptionHandler::$enabled = false;
        }

        // Engine is ready.
        self::$Profiler->sample('Engine Initialisation');
    }

    /**
     * Setter for the Symphony Log and Error Handling system.
     *
     * @since Symphony 2.6.0
     */
    public static function initialiseErrorHandler()
    {
        // Initialise logging
        self::initialiseLog();
        Handlers\GenericExceptionHandler::initialise(self::Log());
        Handlers\GenericErrorHandler::initialise(self::Log());
    }

    /**
     * Accessor for the Symphony instance, whether it be Frontend
     * or Administration.
     *
     * @since Symphony 2.2
     *
     * @throws Exception
     *
     * @return Symphony
     */
    public static function Engine()
    {
        if (class_exists('Administration', false)) {
            return Administration::instance();
        } elseif (class_exists('Frontend', false)) {
            return Frontend::instance();
        } else {
            throw new Exceptions\SymphonyException(__('No suitable engine object found'));
        }
    }

    /**
     * Setter for `$Configuration`. This function initialise the configuration
     * object and populate its properties based on the given `$array`. Since
     * Symphony 2.6.5, it will also set Symphony's date constants.
     *
     * @since Symphony 2.3
     *
     * @param array $data
     *                    An array of settings to be stored into the Configuration object
     */
    public static function initialiseConfiguration(array $data = null)
    {
        $data = $data ?? json_decode(file_get_contents(CONFIG), true);

        self::$Configuration = new Configuration(true);
        self::$Configuration->setArray($data);

        // Set date format throughout the system
        $region = self::Configuration()->get('region');
        define_safe('__SYM_DATE_FORMAT__', $region['date_format']);
        define_safe('__SYM_TIME_FORMAT__', $region['time_format']);
        define_safe('__SYM_DATETIME_FORMAT__', __SYM_DATE_FORMAT__.$region['datetime_separator'].__SYM_TIME_FORMAT__);
        DateTimeObj::setSettings($region);
    }

    /**
     * Accessor for the current `Configuration` instance. This contains
     * representation of the the Symphony config file.
     *
     * @return Configuration
     */
    public static function Configuration()
    {
        return self::$Configuration;
    }

    /**
     * Is XSRF enabled for this Symphony install?
     *
     * @since Symphony 2.4
     *
     * @return bool
     */
    public static function isXSRFEnabled()
    {
        return 'yes' === self::Configuration()->get('enable_xsrf', 'symphony');
    }

    /**
     * Accessor for the current `Profiler` instance.
     *
     * @since Symphony 2.3
     *
     * @return Profiler
     */
    public static function Profiler()
    {
        return self::$Profiler;
    }

    /**
     * Setter for `$Log`. This function uses the configuration
     * settings in the 'log' group in the Configuration to create an instance. Date
     * formatting options are also retrieved from the configuration.
     *
     * @param string $filename (optional)
     *                         The file to write the log to, if omitted this will default to `ACTIVITY_LOG`
     *
     * @throws Exception
     *
     * @return bool|void
     */
    public static function initialiseLog($filename = null)
    {
        if (self::$Log instanceof Log && self::$Log->getLogPath() == $filename) {
            return true;
        }

        if (null === $filename) {
            $filename = ACTIVITY_LOG;
        }

        self::$Log = new Log($filename);
        self::$Log->setArchive(('1' == self::Configuration()->get('archive', 'log') ? true : false));
        self::$Log->setMaxSize(self::Configuration()->get('maxsize', 'log'));
        self::$Log->setFilter(self::Configuration()->get('filter', 'log'));
        self::$Log->setDateTimeFormat(__SYM_DATETIME_FORMAT__);

        if ('1' == self::$Log->open(Log::APPEND, self::Configuration()->get('write_mode', 'file'))) {
            self::$Log->initialise('Symphony Log');
        }
    }

    /**
     * Accessor for the current `Log` instance.
     *
     * @since Symphony 2.3
     *
     * @return Log
     */
    public static function Log()
    {
        return self::$Log;
    }

    /**
     * Setter for `$Cookie`. This will use PHP's parse_url
     * function on the current URL to set a cookie using the cookie_prefix
     * defined in the Symphony configuration. The cookie will last two
     * weeks.
     *
     * This function also defines two constants, `__SYM_COOKIE_PATH__`
     * and `__SYM_COOKIE_PREFIX__`.
     *
     * @deprecated Prior to Symphony 2.3.2, the constant `__SYM_COOKIE_PREFIX_`
     *  had a typo where it was missing the second underscore. Symphony will
     *  support both constants, `__SYM_COOKIE_PREFIX_` and `__SYM_COOKIE_PREFIX__`
     *  until Symphony 3.0
     */
    public static function initialiseCookie()
    {
        define_safe('__SYM_COOKIE_PATH__', DIRROOT === '' ? '/' : DIRROOT);
        define_safe('__SYM_COOKIE_PREFIX_', self::Configuration()->get('cookie_prefix', 'symphony'));
        define_safe('__SYM_COOKIE_PREFIX__', self::Configuration()->get('cookie_prefix', 'symphony'));

        self::$Cookie = new Cookie(__SYM_COOKIE_PREFIX__, TWO_WEEKS, __SYM_COOKIE_PATH__);
    }

    /**
     * Accessor for the current `$Cookie` instance.
     *
     * @since Symphony 2.5.0
     *
     * @return Cookie
     */
    public static function Cookie()
    {
        return self::$Cookie;
    }

    /**
     * Setter for `$ExtensionManager` using the current
     * Symphony instance as the parent. If for some reason this fails,
     * a Symphony Error page will be thrown.
     *
     * @param bool $force (optional)
     *                    When set to true, this function will always create a new
     *                    instance of ExtensionManager, replacing self::$ExtensionManager
     */
    public static function initialiseExtensionManager($force = false)
    {
        if (!$force && self::$ExtensionManager instanceof Managers\ExtensionManager) {
            return true;
        }

        self::$ExtensionManager = new Managers\ExtensionManager();

        if (!(self::$ExtensionManager instanceof Managers\ExtensionManager)) {
            self::throwCustomError(__('Error creating Symphony extension manager.'));
        }
    }

    /**
     * Accessor for the current `$ExtensionManager` instance.
     *
     * @since Symphony 2.2
     *
     * @return ExtensionManager
     */
    public static function ExtensionManager()
    {
        return self::$ExtensionManager;
    }

    /**
     * Setter for `$Database`, accepts a Database object. If `$database`
     * is omitted, this function will set `$Database` to be of the `MySQL`
     * class.
     *
     * @since Symphony 2.3
     *
     * @param stdClass $database (optional)
     *                           The class to handle all Database operations, if omitted this function
     *                           will set `self::$Database` to be an instance of the `MySQL` class
     *
     * @return bool
     *              This function will always return true
     */
    public static function setDatabase(\stdClass $database = null)
    {
        if (self::Database()) {
            return true;
        }

        self::$Database = null !== $database ? $database : new DatabaseWrappers\MySQL();

        return true;
    }

    /**
     * Accessor for the current `$Database` instance.
     *
     * @return MySQL
     */
    public static function Database()
    {
        return self::$Database;
    }

    /**
     * This will initialise the Database class and attempt to create a connection
     * using the connection details provided in the Symphony configuration. If any
     * errors occur whilst doing so, a Symphony Error Page is displayed.
     *
     * @throws SymphonyErrorPage
     *
     * @return bool
     *              This function will return true if the `$Database` was
     *              initialised successfully
     */
    public static function initialiseDatabase()
    {
        self::setDatabase();
        $details = self::Configuration()->get('database');

        try {
            if (!self::Database()->connect($details['host'], $details['user'], $details['password'], $details['port'], $details['db'])) {
                return false;
            }

            if (!self::Database()->isConnected()) {
                return false;
            }

            self::Database()->setPrefix($details['tbl_prefix']);
            self::Database()->setCharacterEncoding();
            self::Database()->setCharacterSet();
            self::Database()->setTimeZone(self::Configuration()->get('timezone', 'region'));

            if (isset($details['query_caching'])) {
                if ('off' == $details['query_caching']) {
                    self::Database()->disableCaching();
                } elseif ('on' == $details['query_caching']) {
                    self::Database()->enableCaching();
                }
            }

            if (isset($details['query_logging'])) {
                if ('off' == $details['query_logging']) {
                    self::Database()->disableLogging();
                } elseif ('on' == $details['query_logging']) {
                    self::Database()->enableLogging();
                }
            }
        } catch (Exceptions\DatabaseException $e) {
            self::throwCustomError(
                $e->getDatabaseErrorCode().': '.$e->getDatabaseErrorMessage(),
                __('Symphony Database Error'),
                AbstractPage::HTTP_STATUS_ERROR,
                'database',
                array(
                    'error' => $e,
                    'message' => __('There was a problem whilst attempting to establish a database connection. Please check all connection information is correct.').' '.__('The following error was returned:'),
                )
            );
        }

        return true;
    }

    /**
     * Accessor for the current `$Author` instance.
     *
     * @since Symphony 2.5.0
     *
     * @return Author
     */
    public static function Author()
    {
        return self::$Author;
    }

    /**
     * Attempts to log an Author in given a username and password.
     * If the password is not hashed, it will be hashed using the sha1
     * algorithm. The username and password will be sanitized before
     * being used to query the Database. If an Author is found, they
     * will be logged in and the sanitized username and password (also hashed)
     * will be saved as values in the `$Cookie`.
     *
     * @see toolkit.Cryptography#hash()
     *
     * @throws DatabaseException
     *
     * @param string $username
     *                         The Author's username. This will be sanitized before use.
     * @param string $password
     *                         The Author's password. This will be sanitized and then hashed before use
     * @param bool   $isHash
     *                         If the password provided is already hashed, setting this parameter to
     *                         true will stop it becoming rehashed. By default it is false.
     *
     * @return bool
     *              true if the Author was logged in, false otherwise
     */
    public static function login($username, $password, $isHash = false)
    {
        $username = trim(self::Database()->cleanValue($username));
        $password = trim(self::Database()->cleanValue($password));

        if (strlen($username) > 0 && strlen($password) > 0) {
            $author = Managers\AuthorManager::fetch('id', 'ASC', 1, null, sprintf(
                "`username` = '%s'",
                $username
            ));

            if (!empty($author) && Cryptography::compare($password, current($author)->get('password'), $isHash)) {
                self::$Author = current($author);

                // Only migrate hashes if there is no update available as the update might change the tbl_authors table.
                if (false === self::isUpgradeAvailable() && Cryptography::requiresMigration(self::$Author->get('password'))) {
                    self::$Author->set('password', Cryptography::hash($password));

                    self::Database()->update(array('password' => self::$Author->get('password')), 'tbl_authors', sprintf(
                        ' `id` = %d',
                        self::$Author->get('id')
                    ));
                }

                self::$Cookie->set('username', $username);
                self::$Cookie->set('pass', self::$Author->get('password'));

                self::Database()->update(
                    array(
                    'last_seen' => DateTimeObj::get('Y-m-d H:i:s'), ),
                    'tbl_authors',
                    sprintf(' `id` = %d', self::$Author->get('id'))
                );

                // Only set custom author language in the backend
                if (class_exists('Administration', false)) {
                    Lang::set(self::$Author->get('language'));
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Symphony allows Authors to login via the use of tokens instead of
     * a username and password. A token is derived from concatenating the
     * Author's username and password and applying the sha1 hash to
     * it, from this, a portion of the hash is used as the token. This is a useful
     * feature often used when setting up other Authors accounts or if an
     * Author forgets their password.
     *
     * @param string $token
     *                      The Author token, which is a portion of the hashed string concatenation
     *                      of the Author's username and password
     *
     * @throws DatabaseException
     *
     * @return bool
     *              true if the Author is logged in, false otherwise
     */
    public static function loginFromToken($token)
    {
        $token = self::Database()->cleanValue($token);

        if (0 == strlen(trim($token))) {
            return false;
        }

        if (6 == strlen($token) || 16 == strlen($token)) {
            $row = self::Database()->fetchRow(0, sprintf(
                "SELECT `a`.`id`, `a`.`username`, `a`.`password`
                FROM `tbl_authors` AS `a`, `tbl_forgotpass` AS `f`
                WHERE `a`.`id` = `f`.`author_id`
                AND `f`.`expiry` > '%s'
                AND `f`.`token` = '%s'
                LIMIT 1",
                DateTimeObj::getGMT('c'),
                $token
            ));

            self::Database()->delete('tbl_forgotpass', sprintf(" `token` = '%s' ", $token));
        } else {
            $row = self::Database()->fetchRow(0, sprintf(
                "SELECT `id`, `username`, `password`
                FROM `tbl_authors`
                WHERE SUBSTR(%s(CONCAT(`username`, `password`)), 1, 8) = '%s'
                AND `auth_token_active` = 'yes'
                LIMIT 1",
                'SHA1',
                $token
            ));
        }

        if ($row) {
            self::$Author = Managers\AuthorManager::fetchByID($row['id']);
            self::$Cookie->set('username', $row['username']);
            self::$Cookie->set('pass', $row['password']);
            self::Database()->update(array('last_seen' => DateTimeObj::getGMT('Y-m-d H:i:s')), 'tbl_authors', sprintf(
                '
                `id` = %d',
                $row['id']
            ));

            return true;
        }

        return false;
    }

    /**
     * This function will destroy the currently logged in `$Author`
     * session, essentially logging them out.
     *
     * @see core.Cookie#expire()
     */
    public static function logout()
    {
        self::$Cookie->expire();
    }

    /**
     * This function determines whether an there is a currently logged in
     * Author for Symphony by using the `$Cookie`'s username
     * and password. If the instance is not found, they will be logged
     * in using the cookied credentials.
     *
     * @see login()
     *
     * @return bool
     */
    public static function isLoggedIn()
    {
        // Check to see if Symphony exists, or if we already have an Author instance.
        if (self::Author() instanceof Author) {
            return true;
        }

        // No author instance found, attempt to log in with the cookied credentials
        return self::$Cookie instanceof Cookie && self::login(self::$Cookie->get('username'), self::$Cookie->get('pass'), true);
    }

    /**
     * Returns the most recent version found in the `/install/migrations` folder.
     * Returns a version string to be used in `version_compare()` if an updater
     * has been found. Returns `FALSE` otherwise.
     *
     * @since Symphony 2.3.1
     *
     * @return string|bool
     */
    public static function getMigrationVersion()
    {
        if (self::isInstallerAvailable()) {

            // @TODO: This needs some serious work. When there are no migration
            // scripts, the last file returned from call to scandir() is 
            // '.gitkeep' which throws off the call to "getVersion" lower down. 
            // There is no sanity checking here to make sure that the file that 
            // is being called is actually a migration script! For now there is
            // a call to preg_match() to validate the file name. Entire migration
            // system needs to be overhauled.

            $migrations = scandir(DOCROOT.'/install/migrations');
            $migration_file = end($migrations);

            if(false == preg_match("@migration_@", $migration_file)) {
                return false;
            }

            $migration_class = 'migration_'.str_replace('.', '', substr($migration_file, 0, -4));

            return call_user_func(array($migration_class, 'getVersion'));
        }

        return false;
    }

    /**
     * Checks if an update is available and applicable for the current installation.
     *
     * @since Symphony 2.3.1
     *
     * @return bool
     */
    public static function isUpgradeAvailable()
    {
        if (self::isInstallerAvailable()) {
            $migration_version = self::getMigrationVersion();
            $current_version = self::Configuration()->get('version', 'symphony');

            return version_compare($current_version, $migration_version, '<');
        }

        return false;
    }

    /**
     * Checks if the installer/upgrader is available.
     *
     * @since Symphony 2.3.1
     *
     * @return bool
     */
    public static function isInstallerAvailable()
    {
        return file_exists(DOCROOT.'/install/index.php');
    }

    /**
     * A wrapper for throwing a new Symphony Error page.
     *
     * This methods sets the `GenericExceptionHandler::$enabled` value to `true`.
     *
     * @see core.SymphonyErrorPage
     *
     * @param string|XMLElement $message
     *                                      A description for this error, which can be provided as a string
     *                                      or as an XMLElement
     * @param string            $heading
     *                                      A heading for the error page
     * @param int               $status
     *                                      Properly sets the HTTP status code for the response. Defaults to
     *                                      `Page::HTTP_STATUS_ERROR`. Use `Page::HTTP_STATUS_XXX` to set this value.
     * @param string            $template
     *                                      A string for the error page template to use, defaults to 'generic'. This
     *                                      can be the name of any template file in the `TEMPLATES` directory.
     *                                      A template using the naming convention of `tpl.*.php`.
     * @param array             $additional
     *                                      Allows custom information to be passed to the Symphony Error Page
     *                                      that the template may want to expose, such as custom Headers etc
     *
     * @throws SymphonyErrorPage
     */
    public static function throwCustomError($message, $heading = 'Symphony Fatal Error', $status = AbstractPage::HTTP_STATUS_ERROR, $template = 'generic', array $additional = array())
    {
        Handlers\GenericExceptionHandler::$enabled = true;
        throw new Exceptions\SymphonyErrorPageException($message, $heading, $template, $additional, $status);
    }

    /**
     * Setter accepts a previous Throwable. Useful for determining the context
     * of a current Throwable (ie. detecting recursion).
     *
     * @since Symphony 2.3.2
     * @since Symphony 2.7.0
     *  This function works with both Exception and Throwable
     *  Supporting both PHP 5.6 and 7 forces use to not qualify the $e parameter
     *
     * @param Throwable $ex
     */
    public static function setException($ex)
    {
        self::$exception = $ex;
    }

    /**
     * Accessor for `self::$exception`.
     *
     * @since Symphony 2.3.2
     *
     * @return Throwable|null
     */
    public static function getException()
    {
        return self::$exception;
    }

    /**
     * Returns the page namespace based on the current URL.
     * A few examples:.
     *
     * /login
     * /publish
     * /blueprints/datasources
     * [...]
     * /extension/$extension_name/$page_name
     *
     * This method is especially useful in couple with the translation function.
     *
     * @see toolkit#__()
     *
     * @return string
     *                The page namespace, without any action string (e.g. "new", "saved") or
     *                any value that depends upon the single setup (e.g. the section handle in
     *                /publish/$handle)
     */
    public static function getPageNamespace()
    {
        if (false !== self::$namespace) {
            return self::$namespace;
        }

        $page = getCurrentPage();

        if (null !== $page) {
            $page = trim($page, '/');
        }

        if ('publish' == substr($page, 0, 7)) {
            self::$namespace = '/publish';
        } elseif (empty($page) && isset($_REQUEST['mode'])) {
            self::$namespace = '/login';
        } elseif (empty($page)) {
            self::$namespace = null;
        } else {
            $bits = explode('/', $page);

            if ('extension' == $bits[0]) {
                self::$namespace = sprintf('/%s/%s/%s', $bits[0], $bits[1], $bits[2]);
            } else {
                self::$namespace = sprintf('/%s/%s', $bits[0], isset($bits[1]) ? $bits[1] : '');
            }
        }

        return self::$namespace;
    }
}
