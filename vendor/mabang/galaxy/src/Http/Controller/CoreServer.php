<?php

namespace Mabang\Galaxy\Http\Controller;

use App;
use Mabang\Galaxy\Common\Configur\Cache;
use Mabang\Galaxy\Common\Handler\InnerServer;
use Mabang\Galaxy\Core\ConfigLoad;
use Mabang\Galaxy\Core\Container;
use Mabang\Galaxy\Core\Log;
use Mix\Vega\Context;
use Swoole;
use Mabang\Galaxy\Common\Annotation\Autowired;

class CoreServer
{
    /**
     * @Autowired()
     * @var InnerServer
     */
    private InnerServer $innerServer;

    public function metrics(Context $ctx)
    {

        $metrics = App::$serverinfo->stats();
        $data = array();
        foreach ($metrics as $k => $v) {
            echo sprintf("%s %s", $k, $v) . "\n";
            $data[] = sprintf("%s %s", $k, $v);
        }
        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $data
        ]);
    }


    public function innerBus(Context $ctx)
    {
        if ($this->innerServer->handler($ctx->uri(), json_decode($ctx->rawData(), 1), App::$innerConfig)) {
            $ctx->JSON(200, [
                'code' => 10200,
                'message' => 'success',
                'data' => true
            ]);
        }
        $ctx->JSON(200, [
            'code' => 10500,
            'message' => 'fail',
            'data' => false]);
          unset($innerServer);
    }

    public function health(Context $ctx)
    {
        if (Cache::instance()->getIncr('mysql-error')>=30){
            App::$serverinfo->reload();
        }
        try {
            $configs = ConfigLoad::findFile();
            foreach ($configs as $key => $val) {
                // $val::init($this->config);
                $ok = $val::health();
          //      log::info("检测 $val " . $val::health());
                if ($ok != "1") {
                    $ctx->JSON(200, [
                        'code' => 10200,
                        'message' => 'success',
                        'data' => "DOWN"
                    ]);
                    print_r($val . " 配置错误 下线");
                    App::$serverinfo->shutdown();
                    return;
                }
                // $val::enableCoroutine();
            }

        } catch (\Throwable $e) {
            log::error($val . " 配置错误 下线");
            App::$serverinfo->shutdown();
        }
        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => "UP"
        ]);

    }
}