<?php

namespace App\XxlJob;

use App\Service\ArchDemoDelayService;
use Galaxy\Common\XxlJob\Handler\BaseHandler;

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