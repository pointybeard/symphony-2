<?php

namespace Symphony\Symphony;

/**
 * @package install
 */

/**
 * The Migration class is extended by updates files that contain the necessary
 * logic to update the current installation to the migration version. In the
 * future it is hoped Migrations will support downgrading as well.
 */

abstract class AbstractMigration
{
    /**
     * The current installed version of Symphony, before updating
     * @var string
     */
    public static $existingVersion = null;

    /**
     * While we are supporting PHP5.2, we can't do this neatly as 5.2
     * lacks late static binding. `self` will always refer to `Migration`,
     * not the calling class, ie. `Migration_202`.
     * In Symphony 2.4, we will support PHP5.3 only, and we can have this
     * efficiency!
     *
     * @return boolean
     *  true if successful, false otherwise
     */
    public static function run($function, $existingVersion = null): bool
    {
        static::$existingVersion = $existingVersion;

        try {
            $canProceed = static::$function();

            return ($canProceed === false) ? false : true;
        } catch (Exceptions\DatabaseException $e) {
            Symphony::Log()->pushToLog('Could not complete upgrading. MySQL returned: ' . $e->getDatabaseErrorCode() . ': ' . $e->getMessage(), E_ERROR, true);

            return false;
        } catch (\Exception $e) {
            Symphony::Log()->pushToLog('Could not complete upgrading because of the following error: ' . $e->getMessage(), E_ERROR, true);

            return false;
        }
    }

    /**
     * Return's the most current version that this migration provides.
     * Note that just because the migration file is 2.3, the migration
     * might only cater for 2.3 Beta 1 at this stage, hence the function.
     *
     * @return string
     */
    public static function getVersion(): ?string
    {
        return null;
    }

    /**
     * Return's the string to this migration's release notes. Like `getVersion()`,
     * this may not be the complete version, but rather the release notes for
     * the Beta/RC.
     *
     * @return string
     */
    public static function getReleaseNotes(): ?string
    {
        return null;
    }

    /**
     * This function will upgrade Symphony from the `self::$existingVersion`
     * to `getVersion()`.
     *
     * @return boolean
     */
    public static function upgrade(): bool
    {
        Symphony::Configuration()->set('version', static::getVersion(), 'symphony');
        Symphony::Configuration()->set('useragent', 'Symphony/' . static::getVersion(), 'general');

        if (Symphony::Configuration()->write() === false) {
            throw new Exceptions\SymphonyException('Failed to write configuration file, please check the file permissions.');
        } else {
            return true;
        }
    }

    /**
     * This function is not implemented yet. It will take the `self::$existingVersion`
     * and downgrade the Symphony install to `getVersion`.
     *
     * @return boolean
     */
    public static function downgrade(): bool
    {
        return true;
    }

    /**
     * Called before an upgrade has started, this function allows migrations to
     * include notices to display the user. These may be warnings about what is
     * about to happen, or a description of what this upgrade provides.
     *
     * @return
     *  An array of strings, where each string will become a list item.
     */
    public static function preUpdateNotes(): array
    {
        return [];
    }

    /**
     * Called after an upgrade has started, this function allows migrations to
     * include notices to display the user. These may be post upgrade steps such
     * as new extensions that are available or required by the current version
     *
     * @return
     *  An array of strings, where each string will become a list item.
     */
    public static function postUpdateNotes(): array
    {
        return [];
    }
}
