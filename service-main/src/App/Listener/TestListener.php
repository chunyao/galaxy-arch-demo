<?php

namespace App\Listener;


use App\Service\MsgProxyService;
use Swoole;
use Galaxy\Core\Log;
use Galaxy\Service\QueueService;
use App\Service\MsgService;


class TestListener
{
    /*变量名固定且必须*/
    public static $queueName = "ARCH_TEST_QUEUE";

    private QueueService $queueService;

    public array $msg;

    private MsgService $msgService;

    private MsgProxyService $msgProxy;


    public function __construct($msg)
    {

        $this->queueService = new QueueService();
        $this->msgService = new MsgService();
        $this->msgProxy = new MsgProxyService();
        $this->msg = $msg;

    }

    /* handler 为固定函数，return true or false，ack 强依赖 */
    public function handler()
    {
        /* 整理 接受msseage 消息*/

        /* 方案一 自己处理消息*/

      //  return $this->msgService->saveMsg($this->msg);


        /* 方案二转发消息*/
     //   return $this->msgProxy->sendMessage("http://192.168.2.21:11181/api/default/testSwooleRabbitMq", $this->msg);
    return true;
    }

    public function __destruct()
    {
        unset($this->msgService);
        unset($this->QueueService);
        unset($this->msgProxy);

    }
}
