<?php

namespace App\Config;

use Mabang\Galaxy\Common\MongoDB\MongoDB;
use Mabang\Galaxy\Core\Once;


class MG2
{

    /**
     * @var MongoDB
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
     * @return MongoDB
     * @throws \Exception
     */
    public static function instance(): MongoDB
    {
        //检测当前类属性$instance是否已经保存了当前类的实例
        if (!isset(self::$instance)) {
            static::$once->do(function () {
                //如果没有,则创建当前类的实例
                $mongo = [
                    'mongo.host' => self::$config['mongo.host'][1],
                    'mongo.port' => self::$config['mongo.port'][1],
                    'mongo.user' => self::$config['mongo.user'][1],
                    'mongo.password' => self::$config['mongo.password'][1],
                    'mongo.replicaset' => self::$config['mongo.replicaset'][1],
                    'mongo.database' => self::$config['mongo.database'][1],
                ];
                self::$instance = new MongoDB($mongo);

            });
        }
        //如果已经有了当前类实例,就直接返回,不要重复创建类实例
        return self::$instance;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public static function enableCoroutine(): void
    {
        $maxOpen = (int) self::$config['mongo.maxOpen'][1];        // 最大开启连接数
        $maxIdle = (int) self::$config['mongo.maxIdle'][1];        // 最大闲置连接数
        $maxLifetime = (int) self::$config['mongo.maxLifetime'][1];  // 连接的最长生命周期
        $waitTimeout = (float) self::$config['mongo.waitTimeout'][1];;   // 从池获取连接等待的时间, 0为一直等待
        self::instance()->startPool($maxOpen, $maxIdle, $maxLifetime, $waitTimeout);
        \Swoole\Runtime::enableCoroutine(); // 必须放到最后，防止触发协程调度导致异常
    }

    public static function health(): string
    {
//        $data = self::instance()->database("mdc_product_online")->table('tb_product')->find(['productId' => "2814182186"]);
//        var_dump($data);
//        if (is_array($data)) {
//            return "1";
//        }
//        return "0";
        return "1";

    }
}