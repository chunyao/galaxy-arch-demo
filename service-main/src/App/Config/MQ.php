<?php

namespace App\Config;

use Galaxy\Common\Mq\Channel\Channel;
use Galaxy\Common\Mq\Rabbitmq;
use Galaxy\Core\Once;


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
    private static $connect;

    /**
     * @return void
     * @throws \Exception
     */
    public static function init(array $config): void
    {
        self::$once = new Once();
        self::$config = $config;


    }

    /**
     * @return MQ
     * @throws \Exception
     */
    public static function instance(): Rabbitmq
    {
        if (!isset(self::$instance)) {
            static::$once->do(function () {
                $host = self::$config['rabbitmq.host'];
                $port = self::$config['rabbitmq.port'];
                $username = self::$config['rabbitmq.username'];
                $password = self::$config['rabbitmq.password'];
                $vhost = self::$config['rabbitmq.send.vhost'][0];
                $mq = new Rabbitmq($host, $port, $username, $password, $vhost, 1);
                self::$instance = $mq;

            });
        }

        return self::$instance;
    }

    /**
     * @return void
     */
    public static function enableCoroutine(): void
    {
//        $maxOpen =2;        // 最大开启连接数
//        $maxIdle = 1;        // 最大闲置连接数
//        $maxLifetime = 60;  // 连接的最长生命周期
//        $waitTimeout = 0;   // 从池获取连接等待的时间, 0为一直等待
//        self::instance()->startPool($maxOpen, $maxIdle, $maxLifetime, $waitTimeout);
//        \Swoole\Runtime::enableCoroutine(); // 必须放到最后，防止触发协程调度导致异常
    }

    public static function health(): string
    {
        return "1";

    }
}