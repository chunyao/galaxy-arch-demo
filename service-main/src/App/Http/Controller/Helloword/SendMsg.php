<?php

namespace App\Http\Controller\Helloword;

use App\Config\MQ;
use Galaxy\Common\Utils\SnowFlakeUtils;
use Mix\Vega\Context;


class SendMsg
{
    private $exchange = "ARCH_TEST2_EXCHANEG";
    private $routekey = "Qwer1234";

    public function __construct()
    {

    }

    public function handler(Context $ctx)
    {

        $id = rand(1, 239368);
        $centerId = rand(1,2);
        $data['id'] = $id;
        $data['generateID']= SnowFlakeUtils::generateID($centerId,3);
        $data['test'] = "testetst" . $id;
        MQ::instance()->publish(json_encode($data), $this->exchange,$this->routekey);
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