<?php

namespace App\XxlJob;

use App\Service\ArchDemoDelayService;
use Mabang\Galaxy\Common\XxlJob\Handler\BaseHandler;
use Swoole\Coroutine as co;

class ArchDemoDelayHandler extends BaseHandler
{
    public function handler(array $params): bool
    {
        for ($i=0;$i<$params['executorParams'];$i++){

            ArchDemoDelayService::instance()->sendMsgDelay1();

        }
        return true;
    }
}