<?php

namespace Galaxy\Common\Configur;

use Galaxy\Core\Once;

class Upgrader
{
    /**
     * @var \Mix\WebSocket\Upgrader
     */
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
     * @return  \Mix\WebSocket\Upgrader
     */
    public static function instance():  \Mix\WebSocket\Upgrader
    {
        if (!isset(self::$instance)) {
            static::$once->do(function () {

                $upgrader =  new Upgrader();

                self::$instance = $upgrader;
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