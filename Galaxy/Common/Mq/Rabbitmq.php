<?php

namespace Galaxy\Common\Mq;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Rabbitmq
{
    protected $channel;

    public function __construct($name = 'default')
    {
        //$this->_logSrv = &load_class('Log', 'core');
        //$this->_conf   = C('rabbitmq');
        $this->_name = $name;
    }

    public function connect()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'why', 'why', 'why', false, 'AMQPLAIN', null, 'en_US', 3.0, 3.0, null, false, 0);
        $this->channel = $connection->channel();
        return true;
    }

    /**
     * @param $messageBody  消息内容
     * @param $exchange     交换机名
     * @param $routeKey     路由键
     * @param array $head
     * @return bool
     */
    public function publish($messageBody, $exchange, $routeKey, $head = [])
    {
        if (!$this->connect()) {
            return false;
        }
        $head = array_merge(array('content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT), $head);
        $message = new AMQPMessage($messageBody, $head);
        $res = $this->channel->basic_publish($message, $exchange, $routeKey);
        return $res;
    }

    /**
     * @param $queue 队列名
     * @param $consumerTag 路由键（这里自动遵循交换机类型匹配规则，默认已经创建好）
     * @param $call_back 回调处理方法
     * @return bool
     */
    public function consumer($queue, $consumerTag, $call_back)
    {
        if (!isset($call_back) || empty($call_back)) {
            return false;
        }

        if (!$this->connect()) {
            return false;
        }

        $this->channel->basic_qos(null, 10, null);

        $this->channel->basic_consume($queue, $consumerTag, false, false, false, false, $call_back);
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    static public function consumer_ack($message)
    {
        echo "\n--------\n";
        echo $message->body;
        echo "\n--------\n";

        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);

        // Send a message with the string "quit" to cancel the consumer.
        if ($message->body === 'quit') {
            $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
        }
    }
}