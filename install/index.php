<?php

declare(strict_types=1);

use Symphony\Symphony;

ini_set('display_errors', '1');

// Turn off preboot when we are installing
putenv('symphony_enable_preboot=0');

// Defines some constants
$domain = $_SERVER['PATH_INFO'] ?? '';
$domain = dirname(rtrim($_SERVER['PHP_SELF'], $domain));
$domain = rtrim($_SERVER['HTTP_HOST'] . $domain, '/\\');
$domain = preg_replace(['/\/{2,}/i', '/install$/i'], ['/', null], $domain);
$domain = rtrim($domain, '/\\');
define('DOMAIN', $domain);

$docroot = rtrim(dirname(__FILE__), '/\\');
$docroot = preg_replace(['/\/{2,}/i', '/install$/i'], ['/', null], $docroot);
$docroot = rtrim($docroot, '/\\');
define('DOCROOT', $docroot);

// Required boot components
define('VERSION', '2.7.10');
define('INSTALL', DOCROOT . '/install');

// Include autoloader:
require DOCROOT . '/vendor/autoload.php';

// Include the boot scripts:
require DOCROOT . '/src/Includes/Compat27.php';
require DOCROOT . '/src/Includes/Functions.php';
require DOCROOT . '/src/Includes/Defines.php';
require DOCROOT . '/src/Includes/Boot.php';

define('INSTALL_LOGS', MANIFEST . '/logs');
define('INSTALL_URL', URL . '/install');

// If Symphony is already installed, run the updater instead
if (false !== realpath(CONFIG)) {
    // System updater
    $script = \Updater::instance();
// If there's no config file, run the installer
} else {
    // System installer
    $script = \Installer::instance();
}

$script->run();
