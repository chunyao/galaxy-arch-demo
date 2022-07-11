<?php

namespace App\Config;
use Galaxy\Common\Mysql\DBLogger;
use Galaxy\Core\Once;
use Galaxy\Core\Database;

class Stock
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
    public static function instance(): Database
    {
        if (!isset(self::$instance)) {
            static::$once->do(function () {
                $dsn = "mysql:host=" . self::$config['mysql.host'][1] . ":" . self::$config['mysql.port'][1]. ";dbname=" . self::$config['mysql.database'][1];
                $username = self::$config['mysql.user'][1];
                $password = self::$config['mysql.password'][1];
                $db = new Database($dsn, $username, $password, [
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    // \PDO::ATTR_EMULATE_PREPARES => false
                ]);
                $db->setLogger(new DBLogger());
                self::$instance = $db;
            });
        }
        return self::$instance;
    }

    /**
     * @return void
     */
    public static function enableCoroutine(): void
    {
        $maxOpen =(int) self::$config['mysql.maxOpen'][1];        // 最大开启连接数
        $maxIdle =(int) self::$config['mysql.maxIdle'][1];        // 最大\闲置连接数
        $maxLifetime =(int) self::$config['mysql.maxLifetime'][1];  // 连接的最长生命周期
        $waitTimeout =(float) self::$config['mysql.waitTimeout'][1];  // 从池获取连接等待的时间, 0为一直等待
        self::instance()->startPool($maxOpen, $maxIdle, $maxLifetime, $waitTimeout);
        \Swoole\Runtime::enableCoroutine(); // 必须放到最后，防止触发协程调度导致异常
    }
    /**
     * health
     * @return void
     */
    public static function health() :string
    {
        try{
            return self::instance()->raw("SELECT 1")->first()[1];
        }catch (\Throwable $ex){
            return "0";
        }


    }

}