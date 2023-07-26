<?php
declare(strict_types=1);//开启严格模式

namespace Mabang\Galaxy\Common\XxlJob\Handler;

use App\Config\RDS;
use App\Repository\Model\BaseModel;
use App\Repository\Model\MdcassociateshopModel;
/**
 * Created by 刘永胜
 * date: 2022/7/7
 * time: 14:47
 */
class BaseHandler
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