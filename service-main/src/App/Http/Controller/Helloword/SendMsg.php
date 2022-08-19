<?php

namespace App\Http\Controller\Helloword;

use App;
use App\Config\MQ;
use Galaxy\Common\Utils\SnowFlakeUtils;
use Mix\Vega\Context;


class SendMsg
{
    private $exchange = "ARCH_TEST2_EXCHANEG";
    private $routekey = "Qwer1234";
    private $snowFlak ;
    public function __construct()
    {

    }

    public function send(Context $ctx)
    {
        $body = $ctx->mustGetJSON();
        MQ::instance()->publish(json_encode($body), $this->exchange, $this->routekey);

        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $body
        ]);
    }

    public function handler(Context $ctx)
    {   $id = mt_rand(0,1000000000);
        $data['id'] =  $id;
        $data['body'] = "With thick pages cut into the s." ;

        MQ::instance()->publish(json_encode($data), $this->exchange, $this->routekey);

        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $data
        ]);

        return "200";
    }

    public function __destruct()
    {

    }

}