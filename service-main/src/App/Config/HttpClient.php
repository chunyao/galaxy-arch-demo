<?php
namespace App\Config;

use Mabang\Galaxy\Core\Once;

class HttpClient
{
    /**
     * @var \Mabang\Galaxy\Common\HttpClient\HttpClient
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
     * @return \Mabang\Galaxy\Common\HttpClient\HttpClient
     * @throws \Exception
     */
    public static function instance(): \Mabang\Galaxy\Common\HttpClient\HttpClient
    {
        //检测当前类属性$instance是否已经保存了当前类的实例
        if (!isset(self::$instance)) {
            static::$once->do(function () {
                //如果没有,则创建当前类的实例
                self::$instance = new \Mabang\Galaxy\Common\HttpClient\HttpClient();

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
        $maxOpen = (int) self::$config['http.maxOpen'][0];        // 最大开启连接数
        $maxIdle = (int) self::$config['http.maxIdle'][0];        // 最大闲置连接数
        $maxLifetime = (int) self::$config['http.maxLifetime'][0];  // 连接的最长生命周期
        $waitTimeout = (float) self::$config['http.waitTimeout'][0];   // 从池获取连接等待的时间, 0为一直等待
        self::instance()->startPool($maxOpen, $maxIdle, $maxLifetime, $waitTimeout);
        \Swoole\Runtime::enableCoroutine(); // 必须放到最后，防止触发协程调度导致异常
    }

    public static function health(): string
    {
        return "1";

    }
}