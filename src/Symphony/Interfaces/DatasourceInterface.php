<?php

//declare(strict_types=1);

namespace Symphony\Symphony\Interfaces;

/**
 * This interface describes the minimum a new Datasource type needs to
 * provide to be able to be used by Symphony.
 *
 * @since Symphony 2.3
 */
interface DatasourceInterface
{
    /**
     * This function return the source of this datasource. It's an artefact
     * of old core objects and for the moment it should return the same
     * value as `getClass`.
     *
     * @return string
     */
    public function getSource();

    /**
     * Return an associative array of meta information about this datasource such
     * creation date, who created it and the name.
     *
     * @return array
     */
    public static function about(): array;

    /**
     * This function is responsible for returning an `XMLElement` so that the
     * `FrontendPage` class can add to a page's XML. It is executed and passed
     * the current `$param_pool` array.
     *
     * @param array $param_pool
     *                          An associative array of parameters that have been evaluated prior to
     *                          this Datasource's execution
     *
     * @return XMLElement
     *                    This Datasource should return an `XMLElement` object
     */
    public function execute(array &$param_pool = null);
}
