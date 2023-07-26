<?php

namespace Mabang\Galaxy\Core;

use Logger;
use Mabang\Galaxy\Common\Configur\Loggers;

/**
 * @method static void fatal(mixed $message)
 * @method static void error(mixed $message)
 * @method static void warn(mixed $message)
 * @method static void notice(mixed $message)
 * @method static void info(mixed $message)
 * @method static void debug(mixed $message)
 *
 */
class Log
{
    private static $log;

    public static function  init(){
        Loggers::init();
    }

    public static function __callStatic($name, $args)
    {
   

        switch ($name) {
            case 'error':
                Loggers::instance()->error(json_encode($args,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                break;
            case 'info':
                Loggers::instance()->info(json_encode($args,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                break;
            case 'warn':
                Loggers::instance()->warn(json_encode($args,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                break;
            case 'debug':
                Loggers::instance()->debug(json_encode($args,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                break;
            case 'fatal':
                Loggers::instance()->fatal(json_encode($args,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                break;
            default:
                break;
        }
    }


}