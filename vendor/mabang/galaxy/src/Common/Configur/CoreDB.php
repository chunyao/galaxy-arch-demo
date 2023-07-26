<?php

namespace Mabang\Galaxy\Common\Configur;

use Galaxy\Common\Configur\config;
use Mabang\Galaxy\Common\Mysql\DBLogger;
use Mabang\Galaxy\Core\Once;
use Mabang\Galaxy\Core\Database;

class CoreDb
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
    public static function init($config): void
    {
        self::$once = new Once();
        self::$config = $config;
    }

    /**
     * @return Database
     */
    public static function instance(): Database
    {
        if (!isset(self::$instance)) {
            static::$once->do(function () {
                $dsn = "mysql:host=".self::$config['mysql.host'].":".self::$config['mysql.port'].";dbname=".self::$config['mysql.database'];
                $username = self::$config['mysql.user'];
                $password = self::$config['mysql.password'];
                $db = new Database($dsn, $username, $password, [
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                    // \PDO::ATTR_EMULATE_PREPARES => false
                ]);
                self::$instance = $db;

                $db->setLogger(new DBLogger());
            });
        }
        return self::$instance;
    }

    /**
     * @return void
     */
    public static function enableCoroutine(): void
    {
        $maxOpen = 30;        // 最大开启连接数
        $maxIdle = 10;        // 最大闲置连接数
        $maxLifetime = 3600;  // 连接的最长生命周期
        $waitTimeout = 0.0;   // 从池获取连接等待的时间, 0为一直等待
        self::instance()->startPool($maxOpen, $maxIdle, $maxLifetime, $waitTimeout);
        \Swoole\Runtime::enableCoroutine(); // 必须放到最后，防止触发协程调度导致异常
    }
}
