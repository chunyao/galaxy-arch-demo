<?php

namespace App\Service;

use Galaxy\Service\QueueService;

class MsgProxy
{
    private $url;

    private $msg;

    private $queueService;

    public function __construct()
    {

        $this->queueService = new QueueService();
    }

    public function sendMessage($url, $msg)
    {
        $sendMsg = array();
        $sendMsg['body'] = $this->msg;
        $nameSpace = $this->queueService->findByQueueName(self::$queueName);
        $sendMsg['handler'] = $nameSpace['mq_handler'];
        $sendMsg['queue'] = $this->msg['queue'];
        $message = array();
        $message['msg'] = $sendMsg;
        $chan = new Swoole\Coroutine\Channel(1);

        go(function () use ($chan, $message) {
            $resp = json_decode((string)App::$httpClient->request('POST', $this->url, ['json' => $message])->getBody());
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