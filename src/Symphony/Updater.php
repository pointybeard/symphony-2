<?php

namespace Symphony\Symphony;

class Updater extends Installer
{

    use Traits\SingletonTrait;

    /**
     * Initialises the language by looking at the existing
     * configuration
     */
    public static function initialiseLang(): void
    {
        Lang::set(Symphony::Configuration()->get('lang', 'symphony'), false);
    }

    /**
     * Overrides the `initialiseLog()` method and writes
     * logs to manifest/logs/update
     */
    public static function initialiseLog($filename = null): void
    {
        if (is_dir(INSTALL_LOGS) || General::realiseDirectory(INSTALL_LOGS, self::Configuration()->get('write_mode', 'directory'))) {
            parent::initialiseLog(INSTALL_LOGS . '/update');
        }
    }

    /**
     * Overrides the default `initialiseDatabase()` method
     * This allows us to still use the normal accessor
     */
    public static function initialiseDatabase(): void
    {
        self::setDatabase();

        $details = Symphony::Configuration()->get('database');

        try {
            Symphony::Database()->connect(
                $details['host'],
                $details['user'],
                $details['password'],
                $details['port'],
                $details['db']
            );
        } catch (Exceptions\DatabaseException $e) {
            self::abort(
                'There was a problem while trying to establish a connection to the MySQL server. Please check your settings.',
                time()
            );
        }

        // MySQL: Setting prefix & character encoding
        Symphony::Database()->setPrefix($details['tbl_prefix']);
        Symphony::Database()->setCharacterEncoding();
        Symphony::Database()->setCharacterSet();
    }

    public function run(): void
    {
        // Initialize log
        if (is_null(Symphony::Log()) || !file_exists(Symphony::Log()->getLogPath())) {
            self::render(new Updater\Page('missing-log'));
        }

        // Get available migrations. This will only contain the migrations
        // that are applicable to the current install.
        $migrations = [];

        foreach (new \DirectoryIterator(INSTALL . '/migrations') as $m) {
            if ($m->isDot() || $m->isDir() || General::getExtension($m->getFilename()) !== 'php') {
                continue;
            }

            $version = str_replace('.php', '', $m->getFilename());

            // Include migration so we can see what the version is
            include_once($m->getPathname());
            $classname = 'migration_' . str_replace('.', '', $version);

            $m = new $classname();

            if (version_compare(Symphony::Configuration()->get('version', 'symphony'), call_user_func(array($m, 'getVersion')), '<')) {
                $migrations[call_user_func(array($m, 'getVersion'))] = $m;
            }
        }

        // The DirectoryIterator may return files in a sporatic order
        // on different servers. This will ensure the array is sorted
        // correctly using `version_compare`
        uksort($migrations, 'version_compare');

        // If there are no applicable migrations then this is up to date
        if (empty($migrations)) {
            Symphony::Log()->pushToLog(
                sprintf('Updater - Already up-to-date'),
                E_ERROR, true
            );

            self::render(new Updater\Page('uptodate'));
        }

        // Show start page
        elseif (!isset($_POST['action']['update'])) {
            $notes = [];

            // Loop over all available migrations showing there
            // pre update notes.
            foreach ($migrations as $version => $m) {
                $n = call_user_func(array($m, 'preUpdateNotes'));
                if (!empty($n)) {
                    $notes[$version] = $n;
                }
            }

            // Show the update ready page, which will display the
            // version and release notes of the most recent migration
            self::render(new Updater\Page('ready', array(
                'pre-notes' => $notes,
                'version' => call_user_func(array($m, 'getVersion')),
                'release-notes' => call_user_func(array($m, 'getReleaseNotes'))
            )));
        }

        // Upgrade Symphony
        else {
            $notes = [];
            $canProceed = true;

            // Loop over all the available migrations incrementally applying
            // the upgrades. If any upgrade throws an uncaught exception or
            // returns false, this will break and the failure page shown
            foreach ($migrations as $version => $m) {
                $n = call_user_func(array($m, 'postUpdateNotes'));
                if (!empty($n)) {
                    $notes[$version] = $n;
                }

                $canProceed = call_user_func(array($m, 'run'), 'upgrade', Symphony::Configuration()->get('version', 'symphony'));

                Symphony::Log()->pushToLog(
                    sprintf('Updater - Migration to %s was %s', $version, $canProceed ? 'successful' : 'unsuccessful'),
                    E_NOTICE, true
                );

                if (!$canProceed) {
                    break;
                }
            }

            if (!$canProceed) {
                self::render(new Updater\Page('failure'));
            } else {
                self::render(new Updater\Page('success', array(
                    'post-notes' => $notes,
                    'version' => call_user_func(array($m, 'getVersion')),
                    'release-notes' => call_user_func(array($m, 'getReleaseNotes'))
                )));
            }
        }
    }
}
