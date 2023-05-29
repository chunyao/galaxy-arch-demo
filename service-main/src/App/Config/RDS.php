<?php
declare(strict_types=1);//开启严格模式

namespace App\Config;
use Mabang\Galaxy\Common\Redis\RDSLogger;
use Mabang\Galaxy\Core\Once;
use Mix\Redis\Redis;

class RDS
{

    /**
     * @var Redis
     */
    private static $instance;

    /**
     * @var Once
     */
    private static $once;

    /**
     * @var Config
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
     * @return Redis
     */
    public static function instance(): Redis
    {
        if (!isset(self::$instance)) {
            static::$once->do(function () {
                $host =  self::$config['redis.host'];
                $port = (int) self::$config['redis.port'];
                $password =  self::$config['redis.password'];
                $database = (int) self::$config['redis.database'];
                $rds = new Redis($host, $port, $password, $database);
                $rds->setLogger(new RDSLogger());
                self::$instance = $rds;
            });
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
    public static function health() :string
    {
        return "1";
     //   if(self::instance()->set("health",true,1)){
     //       return "1";
     //   }
    //    return "1";

    }
}

