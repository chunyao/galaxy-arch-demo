<?php

namespace Galaxy\Core;

use Logger;
/**
 * @method static void emergency(mixed $message)
 * @method static void alert(mixed $message)
 * @method static void critical(mixed $message)
 * @method static void error(mixed $message)
 * @method static void warning(mixed $message)
 * @method static void notice(mixed $message)
 * @method static void info(mixed $message)
 * @method static void debug(mixed $message)
 *
 */
class Log
{


    public static function __callStatic($name, $args)
    {
        $log = Logger::getLogger("Application");

        switch ($name) {
            case 'error':
                $log->error(json_encode($args,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                break;
            case 'info':
                $log->info(json_encode($args,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                break;
            case 'warn':
                $log->warn(json_encode($args,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                break;
            case 'debug':
                $log->debug(json_encode($args,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                break;
            case 'fatal':
                $log->fatal(json_encode($args,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                break;
            default:
                break;
        }
    }


}