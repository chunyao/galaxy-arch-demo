<?php

namespace Mabang\Galaxy\Common\Configur;

use Mabang\Galaxy\Core\Once;
use Logger;
class Loggers
{

    private static $instance;


    private static $once;


    public static function init(): void
    {
        self::$once = new Once();
    }

    public static function instance()
    {
        if (!isset(self::$instance)) {
            static::$once->do(function () {
                $logger = Logger::getLogger("Application");
                self::$instance = $logger;
            });
        }
        return self::$instance;
    }
}