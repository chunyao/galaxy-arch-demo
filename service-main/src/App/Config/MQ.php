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
        $maxOpen = (int) self::$config['redis.maxOpen'];        // 最大开启连接数
        $maxIdle = (int) self::$config['redis.maxIdle'];        // 最大闲置连接数
        $maxLifetime = (int) self::$config['redis.maxLifetime'];  // 连接的最长生命周期
        $waitTimeout = (float) self::$config['redis.waitTimeout'];;   // 从池获取连接等待的时间, 0为一直等待
        self::instance()->startPool($maxOpen, $maxIdle, $maxLifetime, $waitTimeout);
        \Swoole\Runtime::enableCoroutine(); // 必须放到最后，防止触发协程调度导致异常
    }

    public static function health(): string
    {
        return "1";

    }
}