<?php

namespace Galaxy\Core;

use Logger;
/**
 * @method static void alert(mixed $message)
 * @method static void fatal(mixed $message)
 * @method static void error(mixed $message)
 * @method static void warn(mixed $message)
 * @method static void info(mixed $message)
 * @method static void debug(mixed $message)
 *
 */
class Log
{

    private static function skywalking_trace_id()
    {
        if(function_exists('skywalking_trace_id')){
            return empty(skywalking_trace_id())?uniqid():skywalking_trace_id();
        }else{
            return uniqid();
        }
    }
    public static function __callStatic($name, $args)
    {
            $log = Logger::getLogger("Application");
            switch ($name) {
                case 'error':
                    $log->error('traceId: '.self::skywalking_trace_id()." ".json_encode($args,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    break;
                case 'info':
                    $log->info('traceId: '.self::skywalking_trace_id()." ".json_encode($args,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    break;
                case 'warn':
                    $log->warn('traceId: '.self::skywalking_trace_id()." ".json_encode($args,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    break;
                case 'debug':
                    $log->debug('traceId: '.self::skywalking_trace_id()." ".json_encode($args,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    break;
                case 'fatal':
                    $log->fatal('traceId: '.self::skywalking_trace_id()." ".json_encode($args,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    break;
                default:
                    break;
            }
    }
}