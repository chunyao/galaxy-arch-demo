<?php

namespace App\Service;

use Mabang\Galaxy\Service\QueueService;

class MsgProxyService
{
    private $url;

    private $msg;

    private $queueService;

    public function __construct()
    {

        $this->queueService = new QueueService();
    }

    public function sendMessage($url, $msg,$queueName)
    {
        $sendMsg = array();
        $sendMsg['body'] = $msg;
        $nameSpace = $this->queueService->findByQueueName($queueName);
        $sendMsg['handler'] = $nameSpace['mq_handler'];
        $sendMsg['queue'] = $this->msg['queue'];
        $message = array();
        $message['msg'] = $sendMsg;
        $chan = new Swoole\Coroutine\Channel(1);

        go(function () use ($chan, $message,$url) {
            $resp = json_decode((string)App::$httpClient->request('POST',$url, ['json' => $message])->getBody());
            $chan->push($resp);
        });

        // 响应ack
        $r = $chan->pop();

        if ($r->code == "200") {
            return true;
        }
        return false;
    }
}