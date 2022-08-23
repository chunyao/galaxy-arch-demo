<?php
namespace Galaxy\Common\XxlJob\Controller;
use App;
use App\Config\RDS;
use Galaxy\Common\XxlJob\TaskLoad;
use Mix\Vega\Context;
use Swoole\Coroutine as co;

class XxlJobController
{


    public function checkToken(Context $ctx):bool{
        if ($ctx->header('XXL-JOB-ACCESS-TOKEN')==App::$innerConfig['xxl.job.accessToken']){
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

    public function run(Context $ctx){

        if ($this->checkToken($ctx)){
            $param=(array)$ctx->getJSON();
            $tasks = TaskLoad::findFile();
            foreach ($tasks as $key => $val) {
                if ($val == "\\App\Config\\") continue;
                if ($key==$param['executorHandler'])
                {
                    co::create(function () use ($val,$param) {
                        $val::handler($param);
                    });

                }
            }
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
}