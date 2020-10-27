<?php

declare(strict_types=1);

// Set up the Symphony CMS environment
define('DOCROOT', __DIR__);
chdir(DOCROOT);

require DOCROOT.'/src/Includes/Boot.php';

// Begin Symphony proper
symphony($_GET['mode'] ?? null);
