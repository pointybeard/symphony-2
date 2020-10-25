<?php

//declare(strict_types=1);

namespace Symphony\Symphony\Interfaces;

/**
 * This interface describes the minimum a new Event type needs to
 * provide to be able to be used by Symphony.
 *
 * @since Symphony 2.3.1
 */
interface EventInterface
{
    /**
     * Returns the `__CLASS__` on the provided event, this is often
     * used as a way to namespace settings in forms and provide a unique
     * handle for this event type.
     *
     * @return string
     */
    public static function getSource();

    /**
     * Return an associative array of meta information about this event such
     * creation date, who created it and the name.
     *
     * @return array
     */
    public static function about(): array;

    /**
     * The load functions determines whether an event will be executed or not
     * by comparing the Event's action with the `$_POST` data. This function will
     * be called every time a page is loaded that an event is attached too. If the
     * action does exist, it typically calls the `__trigger()` method, otherwise void.
     *
     * @return mixed
     *               XMLElement with the event result or void if the action did not match
     */
    public function load(): ?\XMLElement;

    /**
     * This function actually executes the event, and returns the result of the
     * event as an `XMLElement` so that the `FrontendPage` class can add to
     * a page's XML.
     *
     * @return XMLElement
     *                    This event should return an `XMLElement` object
     */
    public function execute();
}
