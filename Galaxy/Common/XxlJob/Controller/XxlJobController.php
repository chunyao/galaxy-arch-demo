<?php
namespace Galaxy\Common\XxlJob\Controller;
use App;
use App\Config\RDS;
use Galaxy\Common\XxlJob\Handler\XxlJobHandler;
use Galaxy\Common\XxlJob\TaskLoad;
use Mix\Vega\Context;
use Swoole\Coroutine as co;

class XxlJobController
{


    public function checkToken(Context $ctx):bool{

        if ($ctx->header('xxl-job-access-token')==App::$innerConfig['xxl.job.accessToken']){
            return true;
        }else{
            return false;
        }
    }

    public function idleBeat(Context $ctx){
        if ($this->checkToken($ctx)){
            $ctx->JSON(200, [
                'code' => 200,
                'msg' => null
            ]);

        }else{
            $ctx->JSON(200, [
                'code' => 500,
                'msg' => 'fail'
            ]);
        }
        return;
    }

    public function beat(Context $ctx){

        if ($this->checkToken($ctx)){
            $ctx->JSON(200, [
                'code' => 200,
                'msg' => null
            ]);

        }else{
            $ctx->JSON(200, [
                'code' => 500,
                'msg' => 'fail'
            ]);
        }
        return;
    }

    public function run(Context $ctx)
    {
        if ($this->checkToken($ctx))
        {

            $param = (array)$ctx->getJSON();
            co::create(function () use ($param) {
                 XxlJobHandler::handlerCurrent($param);
            });
            $ctx->JSON(200, [
                'code' => 200,
                'msg' => null
            ]);
        }else{
            $ctx->JSON(200, [
                'code' => 500,
                'msg' => 'fail'
            ]);
        }
    }
}