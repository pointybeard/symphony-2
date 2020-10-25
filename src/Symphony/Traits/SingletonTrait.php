<?php

//declare(strict_types=1);

namespace Symphony\Symphony\Traits;

trait SingletonTrait {
    
    protected static $_instance = null;

    public static function instance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }
}
