<?php

namespace Galaxy\Core;

use Logger;

class Log
{


    public static function __callStatic($name, $args)
    {
        $log = Logger::getLogger('app');

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