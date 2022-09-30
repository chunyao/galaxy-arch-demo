<?php

namespace App\Http\Controller\Helloword;

use App;
use App\Config\MQ;
use Galaxy\Common\Configur\SnowFlake;
use Galaxy\Common\Utils\SnowFlakeUtils;
use Mix\Vega\Context;


class SendMsg
{
    private $exchange = "ARCH_TEST2_EXCHANEG";
    private $exchange1 = "ARCH_TEST1_EXCHANEG";
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
    {   $id = SnowFlake::instance()->generateID();
        $data['messageId'] =  $id;
        $data['body'] = "With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s." ;

        $return = MQ::instance()->publish(json_encode($data), $this->exchange, $this->routekey,[],0);
        $return = MQ::instance()->publish(json_encode($data), $this->exchange1, $this->routekey,[],0);
        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $return
        ]);

        return "200";
    }

    public function __destruct()
    {

    }

}