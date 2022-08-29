<?php

namespace App\Service;

use App\Config\MQ;
use Galaxy\Common\Configur\SnowFlake;
use Galaxy\Service\BaseService;

class ArchDemoDelayService extends BaseService
{
    private $exchange = "ARCH_TEST1_EXCHANEG";
    private $routekey = "Qwer1234";

    private $exchange_delay = "ARCH_TEST1_EXCHANGE_DELAY";
    private $routekey_delay = "ARCH_TEST1_KEY_DELAY";

    public function sendMsg1():bool{
        $data['messageId'] =  SnowFlake::instance()->generateID();
        $data['body'] = "With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s." ;
        $head=[];
        MQ::instance()->publish(json_encode($data), $this->exchange, $this->routekey,$head);
        return true;
    }
    public function sendMsgDelay1():bool{
        $data['messageId'] =   SnowFlake::instance()->generateID();
        $data['body'] = "With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s.With thick pages cut into the s." ;
        $head=[];
        MQ::instance()->publish(json_encode($data), $this->exchange_delay, $this->routekey_delay,$head);
        return true;
    }
}