<?php

namespace App\Config;

use Galaxy\Common\Mq\Rabbitmq;


class MQ
{

    /**
     * @var MQ
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
    private static $db;

    /**
     * @return void
     */
    public static function init(array $config): void
    {
        self::$config = $config;
    }

    /**
     * @return MQ
     */
    public static function instance(): Rabbitmq
    {
        if (!isset(self::$instance)) {

                $host = self::$config['rabbitmq.host'];
                $port = self::$config['rabbitmq.port'];

                $username = self::$config['rabbitmq.username'];
                $password = self::$config['rabbitmq.password'];
                $vhost = self::$config['rabbitmq.send.vhost'][0];
                self::$instance = new Rabbitmq($host, $port, $username, $password, $vhost, 1);

        }

        return self::$instance;
    }

    /**
     * @return void
     */
    public static function enableCoroutine(): void
    {

    }

    public static function health(): string
    {
        return "1";

    }
}