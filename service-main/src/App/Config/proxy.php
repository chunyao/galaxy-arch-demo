<?php

namespace App\Config;
use Mabang\Galaxy\Common\Mysql\DBLogger;
use Mabang\Galaxy\Core\Once;
use Mabang\Galaxy\Core\Database;

class Proxy
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
                $dsn = "mysql:host=" . self::$config['mysql.host'][0] . ":" . self::$config['mysql.port'][0]. ";dbname=" . self::$config['mysql.database'][0]; //'mysql:host=192.168.2.224:3306;dbname=swoole'
                $username = self::$config['mysql.user'][0];
                $password = self::$config['mysql.password'][0];
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
        $maxOpen =(int) self::$config['mysql.maxOpen'][0];        // 最大开启连接数
        $maxIdle =(int) self::$config['mysql.maxIdle'][0];        // 最大\闲置连接数
        $maxLifetime =(int) self::$config['mysql.maxLifetime'][0];  // 连接的最长生命周期
        $waitTimeout =(float) self::$config['mysql.waitTimeout'][0];  // 从池获取连接等待的时间, 0为一直等待
        self::instance()->startPool($maxOpen, $maxIdle, $maxLifetime, $waitTimeout);
        \Swoole\Runtime::enableCoroutine(); // 必须放到最后，防止触发协程调度导致异常
    }

    /**
     * health
     * @return void
     */
    public static function health() :string
    {
        return "1";


    }
}