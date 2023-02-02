<?php

namespace Galaxy\Common\Configur;

use Galaxy\Core\Once;
use Mix\WebSocket\Upgrader as mUpgrader;

class Upgrader
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


    public static function instance()
    {
        if (!isset(self::$instance)) {
            static::$once->do(function () {

                self::$instance =   new mUpgrader();

            });
        }
        return self::$instance;
    }

    /**
     * @return void
     */
    public static function enableCoroutine(): void
    {
        \Swoole\Runtime::enableCoroutine(); // 必须放到最后，防止触发协程调度导致异常
    }
}