<?php
declare(strict_types=1);//开启严格模式

namespace Galaxy\Service;


/**
 * Created by 刘永胜
 * date: 2022/7/7
 * time: 14:47
 */
class BaseService
{
    public static $instance = [];

    /**
     * 单利模式
     * @return static
     */
    public static function instance()
    {
        $className = get_called_class();
        isset(self::$instance[$className]) || self::$instance[$className] = new static();
        return self::$instance[$className];
    }

}