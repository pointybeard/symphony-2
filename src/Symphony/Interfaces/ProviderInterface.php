<?php

//declare(strict_types=1);

namespace Symphony\Symphony\Interfaces;

/**
 * This interface is to be implemented by Extensions who wish to provide
 * objects for Symphony to use.
 *
 * @since Symphony 2.3.1
 */
interface ProviderInterface
{
    const DATASOURCE = 'data-sources';
    const EVENT = 'events';

    /**
     * @since Symphony 2.4
     */
    const CACHE = 'cache';

    /**
     * @since Symphony 2.5.0
     */
    const ASSOCIATION_UI = 'association-ui';
    const ASSOCIATION_EDITOR = 'association-editor';

    /**
     * This function should return an associative array of all the
     * Providable objects this extension provides.
     *
     * @since Symphony 2.3
     *
     * @param string $type
     *                     The type of provider object to return, which is one of the `iProvider`
     *                     constants. If `$type` is given, this function should
     *                     only return objects of this `$type`, otherwise all providable objects
     *                     should be returned.
     *
     * @return array
     *               If no providers are found, then an empty array is returned, otherwise
     *               an associative array of classname => human name will be returned.
     *               eg. `array('RemoteDatasource' => 'Remote Datasource')`
     */
    public static function providerOf($type = null);
}