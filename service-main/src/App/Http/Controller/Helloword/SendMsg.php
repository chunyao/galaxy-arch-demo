<?php

namespace App\Http\Controller\Helloword;

use App;
use App\Config\MQ;
use App\Http\Vo\LocalCache\ReqVo;
use co;
use Mabang\Galaxy\Common\Annotation\Route;
use Mabang\Galaxy\Common\Configur\SnowFlake;
use Mix\Vega\Context;


class SendMsg
{
    private $exchange = "ARCH_TEST2_EXCHANEG";
    private $exchange1 = "ARCH_TEST1_EXCHANEG";
    private $routekey = "Qwer1234";
    private $snowFlak;


    public function send(Context $ctx)
    {
        $body = $ctx->mustGetJSON();
        MQ::instance()->poolPublish(json_encode($body), $this->exchange, $this->routekey);

        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $body
        ]);
    }
    /**
     * @Route(route="/msg/send",method="GET",contextType="QUERY",param="\App\Http\Vo\LocalCache\ReqVo")
     */
    public function handler(Context $ctx,ReqVo $reqVo)
    {
        go(function () {
            for ($i = 0; $i < 10000; $i++) {

                $id = SnowFlake::instance()->generateID();
                $data['messageId'] = $id;
                $data['body'] = "With thick pag";
                $s = MQ::instance()->publish(json_encode($data), $this->exchange, $this->routekey, [], 1,1);


            }

        });


        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => 1
        ]);

        return "200";
    }

    public function __destruct()
    {

    }

}