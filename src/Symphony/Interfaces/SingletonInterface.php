<?php

namespace Symphony\Symphony\Interfaces;

/**
 * The Singleton interface contains one function, `instance()`,
 * the will return an instance of an Object that implements this
 * interface.
 */
interface SingletonInterface
{
    public static function instance();
}
