<?php
/**
 * Created by PhpStorm.
 * User: XUMORAN
 * Date: 2022-03-02
 * Time: 20:28
 */

namespace Mabang\Galaxy\Common\XxlJob;


use App;
use Mabang\Galaxy\Common\Utils\Arr;
use Mabang\Galaxy\Common\Utils\GetLocalIp;
use Mabang\Galaxy\Core\Log;

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
            "registryKey" => App::$innerConfig['xxl.job.executor.appname'],       // 执行器AppName
            "registryValue" => "http://$ip:" . APP::$innerConfig['xxl.job.executor.port'],
            // 执行器地址，内置服务跟地址
        ];
        if (isset(APP::$bootConfig['node.ip']) && APP::$bootConfig['node.ip'] != "") {
            $data ["registryValue"] = "http://" . APP::$bootConfig['node.ip'] . ":" . APP::$bootConfig['node.port'];
        }


        self::sendXxlJobRegistry($data, App::$innerConfig['xxl.job.admin.addresses'] . 'api/registry', App::$innerConfig['xxl.job.accessToken']);
        return true;
    }


    public static function XxlJobBeat()
    {
        $url = App::$innerConfig['xxl.job.admin.addresses'] . 'api/beat';
        $ip = GetLocalIp::getIp();
        $data = [
            "registryGroup" => 'EXECUTOR',                     // 固定值
            "registryKey" => App::$innerConfig['xxl.job.executor.appname'],       // 执行器AppName
            "registryValue" => "http://$ip:" . APP::$innerConfig['xxl.job.executor.port'],
            // 执行器地址，内置服务跟地址
        ];
        if (isset(APP::$bootConfig['node.ip']) && APP::$bootConfig['node.ip'] != "") {
            $data ["registryValue"] = "http://" . APP::$bootConfig['node.ip'] . ":" . APP::$bootConfig['node.port'];
        }
        return self::sendXxlJobRegistry($data, $url, App::$innerConfig['xxl.job.accessToken']);
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

        return $res;
    }


    /*
     * 任务处理 处理我弄成 通知服务端
     *
     * */
    public static function XxlJobCallback(array $params)
    {

        $url = App::$innerConfig['xxl.job.admin.addresses'] . 'api/callback';
        $callbackParams = [[
            'logId' => Arr::get($params, 'logId'),
            "logDateTim" => Arr::get($params, 'logDateTime'),
            "handleCode" => 200,
            "handleMsg" => 'success',
            "executeResult" => [
                'code' => Arr::get($params, 'code'),
                'msg' => Arr::get($params, 'msg'),
            ],
        ]];

        Log::info('任务结束');
        return self::sendXxlJobRegistry($callbackParams, $url, App::$innerConfig['xxl.job.accessToken']);
    }
}