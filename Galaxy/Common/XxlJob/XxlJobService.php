<?php
/**
 * Created by PhpStorm.
 * User: XUMORAN
 * Date: 2022-03-02
 * Time: 20:28
 */

namespace  Galaxy\Common\XxlJob;


use App;
use Galaxy\Common\Utils\Arr;
use Galaxy\Common\Utils\GetLocalIp;
use Galaxy\Core\Log;

class XxlJobService
{

    /**
     * 任务的业务场景缓存键名
     *
     * @var string
     */


    public static function XxlJobRegistry(): bool
    {
        $ip = GetLocalIp::getIp();
        $data = [
            "registryGroup" => 'EXECUTOR',                     // 固定值
            "registryKey"   => App::$innerConfig['app.name'],       // 执行器AppName
            "registryValue" => $ip,        // 执行器地址，内置服务跟地址
        ];
        self::sendXxlJobRegistry($data, App::$innerConfig['xxl.job.admin.addresses'].'/api/registry', App::$innerConfig['xxl.job.accessToken']);
        return true;
    }


    public static function XxlJobBeat()
    {
        $url  = App::$innerConfig['xxl.job.admin.addresses'].'/api/beat';
        $ip   = GetLocalIp::getIp();
        $data = [
            "registryGroup" => 'EXECUTOR',                     // 固定值
            "registryKey"   => App::$innerConfig['app.name'],       // 执行器AppName
            "registryValue" => $ip,        // 执行器地址，内置服务跟地址
        ];
        return self::sendXxlJobRegistry($data, $url,  App::$innerConfig['xxl.job.accessToken']);
    }


    public static function XxlJobIdleBeat(array $params)
    {
        $url = App::$innerConfig['xxl.job.admin.addresses'].'/api/idleBeat';
        $ip = GetLocalIp::getIp();
        $data = [
            "registryGroup" => 'EXECUTOR',                     // 固定值
            "registryKey"   => App::$innerConfig['app.name'],       // 执行器AppName
            "registryValue" => $ip,        // 执行器地址，内置服务跟地址
        ];
        return self::sendXxlJobRegistry($data, $url,  App::$innerConfig['xxl.job.accessToken']);
    }


     /*
      * Xxl Job 注册专用
      *
      * */
    public static function sendXxlJobRegistry($params, $url, $token)
    {
        if (!$url || !$token) {
            return false;
        }
        Log::debug('sendXxlJobRegistry:'.json_encode($params, $url, $token));
        $params = json_encode($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'XXL-JOB-ACCESS-TOKEN:' . $token,
            'Content-Type: application/json',
            'Content-Length: ' . strlen($params)
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        $res = curl_exec($ch);
        curl_close($ch);
        Log::debug('sendXxlJob:curl_res:'. json_encode($res));

        return $res;
    }



    public function handlerCurrent($handler, $taskId, $params)
    {
        try {
            $className = $handler;
            if (!class_exists($className)) {
                Log::error(sprintf('%s msg handler class not exists', $taskId), [
                    'handler' => $handler
                ]);
                return false;
            }
            $obj = new $className;
            $return = $obj->taskRun($params);
        } catch (\Throwable $e) {
            Log::error(sprintf('deal %s msg error', $taskId), [
                'msg' => $params,
                'e_file' => $e->getFile(),
                'e_line' => $e->getLine(),
                'e_code' => $e->getCode(),
                'e_msg' => $e->getMessage(),
                'e_trace' => $e->getTraceAsString()
            ]);

            $return = false;
        }
        return $return;
    }




    /**
     * 根据任务ID，获取消息业务场景信息
     *
     * @param string $queueName
     * @return mixed
     * @throws \Exception
     */
    /* public static function getSceneInfoByTaskId(int $taskId)
     {
         $sceneInfo = self::getSceneInfo('task_id', $taskId);

         if (!$sceneInfo) {
             Log::error('task_scene_not_exists', ['task_id' => $taskId]);
             throw new \Exception('非法的消息业务场景');
         }

         return $sceneInfo;
     }
     */

    /**
     * 获取场景配置
     *
     * @param string $field
     * @param string $value
     * @return mixed
     */
    public static function getSceneInfo(string $field, string $value)
    {
        $redis = Redis::connection('rabbitmq-common');
        if (!$redis->exists(self::REDIS_TASK_XXLJOB_SCENE_CACHE_KEY)) {
            self::setSceneCache();
        }

        $cache = $redis->get(self::REDIS_TASK_XXLJOB_SCENE_CACHE_KEY);
        $rows = json_decode($cache, true);
        Log::debug('rows:', [$rows]);
        $result = collect($rows)->firstWhere($field, $value);
        Log::debug('resut:', [$result]);
        return $result;
    }


    /**
     * 设置任务的业务场景缓存
     *
     * @return bool
     */
    /* public static  function setSceneCache() : bool
     {
         $rows       = TaskXxljobSceneModel::query()->get()->toArray();
         $redis      = Redis::connection('rabbitmq-common');
         $response   = $redis->setex(self::REDIS_TASK_XXLJOB_SCENE_CACHE_KEY, 1800, json_encode($rows, JSON_UNESCAPED_UNICODE));

         return $response ? true : false;
     }*/

}