<?php

namespace Mabang\Common\Rpc;

class Client
{
    public static $instance = [];

    /**
     * 单利模式
     * @return static
     */
    public static function instance()
    {
        $className = get_called_class();
        isset(self::$instance[$className]) || self::$instance[$className] = new static();
        return self::$instance[$className];
    }

    public static function post(string $serviceName, array $data)
    {

    }
}