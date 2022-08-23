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
            "registryValue" => "http://$ip:".APP::$innerConfig['xxl.job.executor.port'],        // 执行器地址，内置服务跟地址
        ];

        self::sendXxlJobRegistry($data, App::$innerConfig['xxl.job.admin.addresses'].'api/registry', App::$innerConfig['xxl.job.accessToken']);
        return true;
    }


    public static function XxlJobBeat()
    {
        $url  = App::$innerConfig['xxl.job.admin.addresses'].'api/beat';
        $ip   = GetLocalIp::getIp();
        $data = [
            "registryGroup" => 'EXECUTOR',                     // 固定值
            "registryKey"   => App::$innerConfig['app.name'],       // 执行器AppName
            "registryValue" => "http://$ip:".APP::$innerConfig['xxl.job.executor.port'],   // 执行器地址，内置服务跟地址
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


    /*
     * 任务处理 处理我弄成 通知服务端
     *
     * */
    public static function XxlJobCallback(array $params)
    {

        $url   = App::$innerConfig['xxl.job.admin.addresses'].'api/callback';
        $callbackParams = [[
            'logId'          => Arr::get($params, 'logId'),
            "logDateTim"     => Arr::get($params, 'logDateTime'),
            "handleCode"     => 200,
            "handleMsg"      =>  'success',
            "executeResult"  => [
                'code' => 200,
                'msg'  =>  'success',
            ],
        ]];


        return self::sendXxlJobRegistry($callbackParams, $url,  App::$innerConfig['xxl.job.accessToken']);
    }
}