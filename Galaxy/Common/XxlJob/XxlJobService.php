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
            "registryGroup" => env('XXL_JOB_REGISTRY_GROUP', 'EXECUTOR'),                     // 固定值
            "registryKey" => env('XXL_JOB_REGISTRY_KEY', App::$innerConfig['app.name']),       // 执行器AppName
            "registryValue" => env('XXL_JOB_REGISTRY_VALUE', 'http://' . $ip . ':' . App::$bootConfig['management.server.port'] . '/'),        // 执行器地址，内置服务跟地址
        ];
        self::sendXxlJobRegistry($data, App::$innerConfig['xxl.job.admin.addresses'], App::$innerConfig['xxl.job.accessToken']);
        return true;
    }


    public static function XxlJobBeat()
    {
        $url = '/beat';
        $ip = GetLocalIp::getIp();
        $data = [
            "registryGroup" => env('XXL_JOB_REGISTRY_GROUP', 'EXECUTOR'),                     // 固定值
            "registryKey" => env('XXL_JOB_REGISTRY_KEY', App::$innerConfig['app.name']),       // 执行器AppName
            "registryValue" => env('XXL_JOB_REGISTRY_VALUE', $ip),        // 执行器地址，内置服务跟地址
        ];
        return self::sendXxlJob($data, $url);
    }


    public static function XxlJobIdleBeat(array $params)
    {
        $url = '/idleBeat';
        $ip = GetLocalIp::getIp();
        $data = [
            "registryGroup" => env('XXL_JOB_REGISTRY_GROUP', 'EXECUTOR'),                     // 固定值
            "registryKey" => env('XXL_JOB_REGISTRY_KEY', App::$innerConfig['app.name']),       // 执行器AppName
            "registryValue" => env('XXL_JOB_REGISTRY_VALUE', $ip),        // 执行器地址，内置服务跟地址
        ];
        return self::sendXxlJob($data, $url);
    }

    /* public static  function XxlJobCallback(array $params)
     {
         $logId =  Arr::get($params, 'logId');
         $taskXxlJobLogModel = TaskXxljobLogModel::getOne($logId);
         if (!$taskXxlJobLogModel)
         {
             return false;
         }
         $taskStatus  = Arr::get($params, 'taskStatus');
         $taskXxlJobLogModel->task_status = $taskStatus;
         $handleMsg    = Arr::get($params, 'handleMsg');
         $handleCode   = Arr::get($params, 'handleCode');
         //若当前code 为空，则为异步，可直接返回，
         if (!$handleCode)
         {
             return true;
         }
         // todo 考虑优化成队列方式处理，此处生产队列，
         // MQ::publish('SYSTEM_XXL_JOB_REGISTRY_CODE', ['title'=>'延迟20秒追加再次消费', 'xxljob_config'=>$XxlJobConfig ],1000);
         $url = '/callback';
         $callbackParams = [[
             'logId'          => Arr::get($params, 'logId'),
             "logDateTim"     => Arr::get($params, 'logDateTime'),
             "handleCode"     => $handleCode,
             "handleMsg"      =>  $handleMsg,
             "executeResult" => [
                 'code' => $handleCode,
                 'msg'  =>  $handleMsg,
             ],
         ]];
         Log::debug('sendXxlJob:', [$params, $url]);
         return self::sendXxlJob([$params], $url);
     }

     public static function sendXxlJob($params, $url)
     {
         Log::debug('sendXxlJob:', [$params, $url]);
         $url    = App::$innerConfig['xxl.job.admin.addresses'].'/api/'. $url;
         $params = json_encode($params);
         $token  = env('XXL_JOB_ACCESS_TOKEN', App::$innerConfig['xxl.job.accessToken']);

         Log::debug('sendXxlJob:TOKEN:', [$token, $url]);

         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_HTTPHEADER, array(
             'XXL-JOB-ACCESS-TOKEN:'.$token,
             'Content-Type: application/json',
             'Content-Length: ' . strlen($params)
         ));
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
         curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
         curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

         $res = curl_exec($ch);
         curl_close($ch);

         Log::debug('sendXxlJob:curl_res:', [$res]);

         return $res;
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
        Log::debug('sendXxlJobRegistry:', [$params, $url, $token]);
        $params = json_encode($params);
        $url = $url . '/api/registry';
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
        Log::debug('sendXxlJob:curl_res:', [$res]);

        return $res;
    }


    /* public  function XxlRun($params):bool
     {
         //ps: $params [{"jobId":9,"executorHandler":null,"executorParams":null,"executorBlockStrategy":"SERIAL_EXECUTION","executorTimeout":0,"logId":1562,"logDateTime":1648891069975,"glueType":"GLUE_SHELL","glueSource":"#!/bin/bash\necho \"xxl-job: hello shell\"\n\necho \"脚本位置：$0\"\necho \"任务参数：$1\"\necho \"分片序号 = $2\"\necho \"分片总数 = $3\"\n\necho \"Good bye!\"\nexit 0","glueUpdatetime":1648870329000,"broadcastIndex":0,"broadcastTotal":1}]
         //获取任务信息，匹配分发 todo 任务处理逻辑
         //step 1 . 获取任务id
         $taskId  =  Arr::get($params, 'jobId');
         if (!$taskId)
         {
             return false;
         }
         ## step 2 . 根据任务ID 分配执行方式
         $taskScene          = self::getSceneInfoByTaskId($taskId);
         ## step 3 . 增加日志
         $logId = Arr::get($params, 'logId');
         Log::debug('task scene:', [$taskScene, $taskId, $logId]);
         $taskXxlJobLogModel = TaskXxljobLogModel::getOne($logId);
         if (!$taskXxlJobLogModel)
         {
             $taskXxlJobLogModel = new TaskXxljobLogModel();
             $taskXxlJobLogModel->task_id    = $taskId;
             $taskXxlJobLogModel->log_id      = $logId;
             $taskXxlJobLogModel->glue_type  = Arr::get($params, 'glueType');
             $taskXxlJobLogModel->executor_params_all = json_encode($params, true);
             //$taskXxlJobLogModel->task_status  =
             $taskXxlJobLogModel->save();
         }
         $taskHandler = Arr::get($taskScene, 'task_handler');
         $taskType = Arr::get($taskScene, 'task_type');
         $mqCode = Arr::get($taskScene, 'mq_code');
         if ($taskScene && $taskHandler  && $taskType == TaskXxljobLogModel::TASK_TYPE_SYNC)
         {
             $taskStatus  = TaskXxljobLogModel::TASK_STATUS_ACKED;
             $result  =  $this->handlerCurrent($taskHandler, $taskId, $params);
             $taskStatus  = $result ? TaskXxljobLogModel::TASK_STATUS_ACKED : TaskXxljobLogModel::TASK_STATUS_FAIL;
         }
         if ($taskScene && $taskType == TaskXxljobLogModel::TASK_TYPE_ASYN && $mqCode )
         {
             $taskStatus  = TaskXxljobLogModel::TASK_STATUS_IN_PROCESS;
             MQ::publish($mqCode, $params);
         }
         Log::debug(sprintf('-------- end deal %s msg --------', $taskId));
         $taskXxlJobLogModel->task_status = $taskStatus;
         $result_code  = isset($result)?($result?'200':'401'):'';

         $taskXxlJobLogModel->save();
         $callbackParams =[
             'taskStatus'     => $taskStatus,
             'taskId'         => $taskId,
             'logId'          => $logId,
             "logDateTim"     => Arr::get($params, 'logDateTime'),
             "handleCode"     => $result_code,
             "handleMsg"      => isset($result)?($result?'success':'fail'):'',
             "executeResult" => [
                 'code' => $result_code,
                 'msg'  =>  isset($result)?($result?'success':'fail'):'',
             ],
         ];
         ## step 4 . 插入回调队列处理

         //任务处理完成，执行回调
         // XxlJobService::XxlJobCallback($callbackParams);
         return true;
     }
 */

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