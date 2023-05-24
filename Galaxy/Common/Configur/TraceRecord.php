<?php

namespace Galaxy\Common\Configur;

use Galaxy\Core\Once;
use Galaxy\Http\Middleware\All\TraceRecordMiddleware;


class TraceRecord
{

    private static $instance;

    /**
     * @var Once
     */
    private static $once;

    /**
     * @return void
     */
    public static function init(): void
    {
        self::$once = new Once();

    }

    /**
     * @return TraceRecordMiddleware
     */
    public static function instance(): TraceRecordMiddleware
    {
        if (!isset(self::$instance)) {
            static::$once->do(function () {

                self::$instance = new TraceRecordMiddleware();
            });
        }
        return self::$instance;
    }

    /**
     * @return void
     */
    public static function enableCoroutine(): void
    {

    }
}