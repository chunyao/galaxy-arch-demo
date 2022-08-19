<?php

namespace MabangSdk\XxlJob\Support\XxlJob\Job;

use App\Support\Facades\MonitorFacade;
use App\Logics\MonitorServices;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use MabangSdk\MQ\Facades\MQ;
use MabangSdk\XxlJob\Support\XxlJob\XxlJobService;

class XxlJobRegistryHandler implements  ShouldQueue
{

    public function run($params)
    {
        $XxlJobConfig  = config('xxljobconfig')?? Arr::get($params, 'xxljob_config');
        if (empty($XxlJobConfig))
        {
            return true;
        }
        // ------ 分割线 生产一条延迟队列，20秒后执行---------------------------------------------
        MQ::publish('SYSTEM_XXL_JOB_REGISTRY_CODE', ['title'=>'延迟20秒追加再次消费', 'xxljob_config'=>$XxlJobConfig ],20000);

        // ------ 分割线 ---------------------------------------------
        //继续执行当前 刷新操作
        $xxlJobUrl  = env('XXL_JOB_API_URL', 'http://192.168.2.61:31080/xxl-job-admin/api'). '/registry';
        foreach ($XxlJobConfig as $val) {
            $token  = Arr::get($val, 'XXL_JOB_ACCESS_TOKEN', '');
            $params = Arr::get($val, 'params', []);
            $registryResult  = XxlJobService::sendXxlJobRegistry($params, $xxlJobUrl , $token );
            Log::debug('registryResult:', [$registryResult, $params, $xxlJobUrl, $token]);
            //监听，注册失败，发送消息提醒负责人
            if (!$registryResult || (isset($registryResult['code']) && $registryResult['code'] != 200))
            {
                //注册失败通知
                $monitorInfo  = [
                    'CHANNEL_ID'  => 5,
                    'CHANNEL_KEY' => 'php_mabang_v2_system',
                    'title'       => 'XXL Job 任务中心注册 失败',
                    'content'     => '执行器：'. Arr::get($val, 'registryKey'). ' 注册失败（'.json_encode($val).'）',
                    'scene_code'  => 'XXL_JOB_REGISTRY',
                    'send_type'   => '1',
                    'companyId'   => 1,
                    'send_users'  => '',
                ];

                $resultMonitor =  MonitorFacade::sendWechat(
                    $monitorInfo,
                    Arr::get(MonitorServices::sendAuth($monitorInfo, 1), 'access_token', '')
                );
                Log::debug('resultMonitor:', [$monitorInfo, $resultMonitor]);
            }
        }
        return true;
    }
}