<?php

//declare(strict_types=1);

require DOCROOT . '/vendor/autoload.php';

use Symphony\Symphony;

// Set appropriate error reporting:
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

require DOCROOT . '/src/Includes/Compat27.php';
require DOCROOT . '/src/Includes/Functions.php';
require DOCROOT . '/src/Includes/Defines.php';

// Redirect to installer if it exists
if (false === realpath(CONFIG)) {
    $inInstaller = defined("INSTALL");

    if (false == $inInstaller && true == \Symphony::isInstallerAvailable()) {
        header(sprintf('Location: %s/install/', URL));
        exit;
    } elseif (false == $inInstaller) {
        throw new Symphony\Exceptions\SymphonyException("Could not locate Symphony configuration file. Please check manifest/config.json exists.");
    }
} else {

    // Load configuration file:
    \Symphony::initialiseConfiguration();
    \Symphony::initialiseErrorHandler();
    \Symphony::initialiseDatabase();
    \Symphony::initialiseExtensionManager();

    // Report all errors
    if ('yes' === \Symphony::Configuration()->get('error_reporting_all', 'symphony')) {
        error_reporting(E_ALL);
    }

    // Handle custom admin paths, #702
    $adminPath = \Symphony::Configuration()->get('admin-path', 'symphony');
    $adminPath = (null === $adminPath) ? 'symphony' : $adminPath;
    // getCurrentPage() always starts with / #2522
    $adminRegExp = '%^\/'.preg_quote($adminPath).'\/%';

    if (true == preg_match($adminRegExp, (string) getCurrentPage())) {
        $_GET['symphony-page'] = preg_replace($adminRegExp, '', (string) getCurrentPage(), 1);

        if ('' == $_GET['symphony-page']) {
            unset($_GET['symphony-page']);
        }

        $_GET['mode'] = $_REQUEST['mode'] = 'administration';
    }

    /*
     * Returns the URL + /symphony. This should be used whenever the a developer
     * wants to link to the Symphony root
     * @since Symphony 2.2
     * @var string
     */
    define_safe('SYMPHONY_URL', sprintf("%s/%s", URL, $adminPath));

    /*
     * Overload the default Symphony launcher logic.
     * @delegate ModifySymphonyLauncher
     * @since Symphony 2.5.0
     * @param string $context
     * '/all/'
     */
    \Symphony::ExtensionManager()->notifyMembers(
        'ModifySymphonyLauncher',
        '/all/'
    );

    // Use default launcher:
    if (false === defined('SYMPHONY_LAUNCHER')) {
        define('SYMPHONY_LAUNCHER', 'symphony_launcher');
    }
}

/**
 * This will iterate over each extension and look to see if
 * there is a composer autoload file. If there is, it will include that
 * autoloader ensuring that libraries are available as soon as the Symphony
 * core is instanciated.
 */
if (false != realpath(EXTENSIONS)) {
    foreach ((new \DirectoryIterator(EXTENSIONS)) as $e) {
        if (true == $e->isDot() || false == $e->isDir()) {
            continue;
        }

        // See if there is a composer.json and a vendor/autoload.php file
        if (true == file_exists($e->getPathname().'/composer.json') && true == file_exists($e->getPathname().'/vendor/autoload.php')) {
            include_once $e->getPathname().'/vendor/autoload.php';
        }
    }
}
