<?php

namespace App\Config;

use Galaxy\Common\Mq\Rabbitmq;
use Galaxy\Core\Once;
use Mix\Database\Database;

class MQ
{

    /**
     * @var Database
     */
    private static $instance;

    /**
     * @var Once
     */
    private static $once;

    /**
     * @var config
     */
    private static $config;

    /**
     * @return void
     */
    public static function init(array $config): void
    {
        self::$once = new Once();
        self::$config = $config;
    }

    /**
     * @return Database
     */
    public static function instance(): Rabbitmq
    {
        $host = self::$config['rabbitmq.host'];
        $port = self::$config['rabbitmq.port'];

        $username = self::$config['rabbitmq.username'];
        $password = self::$config['rabbitmq.password'];

        $mq = new Rabbitmq($host, $port, $username, $password, "arch", null);

        self::$instance = $mq;
        return self::$instance;
    }

    /**
     * @return void
     */
    public static function enableCoroutine(): void
    {

    }

}