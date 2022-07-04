<?php

namespace App\Listener;

use Swoole;
use Galaxy\Core\Log;
use Galaxy\Service\QueueService;
use App\Service\MsgService;
use App;

class TestListener
{

    /*变量名固定且必须*/
    public static $queueName = "JAVA_TEST_1000000001_QUEUE";

    private QueueService $queueService;

    public array $msg;

    private MsgService $msgService;

    private $url;

    public function __construct($msg)
    {

        $this->queueService = new QueueService();

        $this->msgService = new MsgService();

        $this->msg = $msg;

    }

    /* handler 为固定函数，return true or false，ack 强依赖 */
    public function handler()
    {
        $convertMsg = $this->msg;
        $this->url = "http://192.168.2.21:11181/api/default/testSwooleRabbitMq";
        $convertMsg['msg_id'] = $this->msg['id'];
        unset($convertMsg['id']);
        $nameSpace = $this->queueService->findByQueueName(self::$queueName);
        $this->msgService->saveMsg($convertMsg);
        $sendMsg = array();

        $sendMsg['body'] = $this->msg;

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

    public function __destruct()
    {
        unset($this->msgService);
        unset($this->QueueService);
    }
}
