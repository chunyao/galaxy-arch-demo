<?php

namespace App\Config;

use Mabang\Galaxy\Core\Once;
use Mabang\Galaxy\Common\ES\LibES;

class ES
{

    /**
     * @var MGDB
     */
    private static $instance;

    /**
     * @var Once
     */
    private static $once;

    /**
     * @var config
     */
    private static array $config;

    /**
     * @return void
     */
    public static function init(array $config): void
    {
        self::$once = new Once();
        self::$config = $config;
    }

    /**
     * @return MGDB
     */
    public static function instance(): LibES
    {


        //检测当前类属性$instance是否已经保存了当前类的实例
        if (!isset(self::$instance)) {
            static::$once->do(function () {
                //如果没有,则创建当前类的实例
                self::$instance = new LibES(self::$config);
            });
        }
        //如果已经有了当前类实例,就直接返回,不要重复创建类实例

        return self::$instance;
    }

    /**
     * @return void
     */
    public static function enableCoroutine(): void
    {

    }

    /**
     * health
     * @return void
     */
    public static function health(): string
    {

        try {
            return "1";
            // if (self::instance()->existsIndex()){return "1";}

        } catch (\Throwable $ex) {

        }
        return "1";
    }
}