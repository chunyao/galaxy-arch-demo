<?php

namespace App\Config;
use Galaxy\Common\Mysql\DBLogger;
use Galaxy\Core\Once;
use Galaxy\Core\Database;

class SQL
{

    /**
     * @var Database
     */
    private static $instance;
    private static int $num;

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
        self::$num=3;
    }

    /**
     * @return Database
     */
    public static function instance(): Database
    {

        if (!isset(self::$instance)) {
            static::$once->do(function () {
                $dsn = "mysql:host=" . self::$config['mysql.host'][self::$num] . ":" . self::$config['mysql.port'][self::$num]. ";dbname=" . self::$config['mysql.database'][self::$num]; //'mysql:host=192.168.2.224:3306;dbname=swoole'
                $username = self::$config['mysql.user'][self::$num];
                $password = self::$config['mysql.password'][self::$num];
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
        $maxOpen =(int) self::$config['mysql.maxOpen'][self::$num];        // 最大开启连接数
        $maxIdle =(int) self::$config['mysql.maxIdle'][self::$num];        // 最大\闲置连接数
        $maxLifetime =(int) self::$config['mysql.maxLifetime'][self::$num];  // 连接的最长生命周期
        $waitTimeout =(float) self::$config['mysql.waitTimeout'][self::$num];  // 从池获取连接等待的时间, 0为一直等待
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