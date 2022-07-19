<?php

namespace Galaxy\Http\Controller;

use App;
use Galaxy\Common\Handler\InnerServer;
use Galaxy\Core\ConfigLoad;
use Galaxy\Core\Log;
use Mix\Vega\Context;

class CoreServer
{
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

        $innerServer = new InnerServer($ctx->uri(), json_decode($ctx->rawData(), 1), App::$innerConfig);

        if ($innerServer->handler()) {
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
        try {
            $configs = ConfigLoad::findFile();
            foreach ($configs as $key => $val) {
                // $val::init($this->config);
                $ok = $val::health();
                log::info("检测 $val " . $val::health());
                if ($ok != "1") {
                    $ctx->JSON(200, [
                        'code' => 10200,
                        'message' => 'success',
                        'data' => "DOWN"
                    ]);
                    log::error($val . " 配置错误 下线");
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