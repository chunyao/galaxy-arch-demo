<?php

namespace App\Http\Controller\Helloword;

use App\Config\MQ;


class SendMsg
{
    private $exchange = "ARCH_TEST_EXCHANEG";
    private $routekey = "Qwer1234";

    public function __construct()
    {

    }

    public function handler()
    {

        $id = rand(1, 239368);
        $data['test'] = "testetst" . $id;
        $data['id'] = $id;

        MQ::instance()->publish(json_encode($data), $this->exchange,$this->routekey);
        return "200";
    }

    public function __destruct()
    {

    }

}