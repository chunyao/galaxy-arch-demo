<?php
declare(strict_types=1);//开启严格模式

namespace Mabang\Galaxy\Repository\Model;

/**
 * Created by 刘永胜
 * date: 2022/7/8
 * time: 11:15
 */
class BaseModel
{
    //默认100张分表
    public int $subTable = 100;

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