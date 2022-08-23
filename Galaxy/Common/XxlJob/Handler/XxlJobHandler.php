<?php
declare(strict_types=1);//开启严格模式

namespace Galaxy\Common\XxlJob\Handler;

use App\Config\RDS;
use App\Repository\Model\BaseModel;
use App\Repository\Model\MdcassociateshopModel;
use Galaxy\Common\XxlJob\XxlJobService;
use Galaxy\Core\Log;
use Hyperf\Utils\Arr;
use Swoole\Coroutine as co;

/**
 * Created by 刘永胜
 * date: 2022/7/7
 * time: 14:47
 */
class XxlJobHandler
{
    public static function handlerCurrent( array  $params):bool
    {
        $handler   = Arr::get($params, 'executorHandler');
        $className = "\\App\\XxlJob\\". $handler;
        $logId     = Arr::get($params, 'logId');
        try {
            //SetCompanyGroupId:Redis","executorBlockStrategy":"SERIAL_EXECUTION",
            //"executorTimeout":0,"logId":1572,"logDateTime":1649217636906,
            //"glueType":"BEAN","glueSource":null,"glueUpdatetime":1648200920000,"broadcastIndex":0,"broadcastTotal":1}
            if (!class_exists($className)) {
                Log::error(sprintf('%s msg handler class not exists', $logId), [
                    'handler' => $handler
                ]);
                return false;
            }

            $obj = new $className;
            $return =  $obj::instance()->handler($params);

        } catch (\Throwable $e) {
            Log::error(sprintf('deal %s msg error', $logId), [
                'msg'    => $params,
                'e_file' => $e->getFile(),
                'e_line' => $e->getLine(),
                'e_code' => $e->getCode(),
                'e_msg' => $e->getMessage(),
                'e_trace' => $e->getTraceAsString()
            ]);

            $return = false;
        }
        //任务处理回调
        co::create(function () use ($params)
        {
            XxlJobService::XxlJobCallback($params);
        });

        return $return;
    }






}