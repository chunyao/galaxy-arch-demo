<?php

namespace Galaxy\Common\Mq;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Swoole;

class Rabbitmq
{
    protected $channel;
    protected $ch;
    protected $con;
    protected $host;
    protected $port;
    protected $username;
    protected $password;
    protected $vhost;

    public function __construct($host, $port, $username, $password, $vhost, $channel)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->vhost = $vhost;
        $this->ch = $channel;
    }

    public function connect()
    {
        $this->con = new AMQPStreamConnection($this->host, $this->port, $this->username, $this->password, $this->vhost, false, 'AMQPLAIN', null, 'en_US', 3.0, 3.0, null, false, 10);
        $this->channel = $this->con->channel(12);

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

        $head = array_merge(array('content_type' => 'text/plain', 'content_encoding' => 'gzip', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT), $head);
        $message = new AMQPMessage($messageBody, $head);
        $res = $this->channel->basic_publish($message, $exchange, $routeKey);
        // 响应ack
        return $res;
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->con->close();
    }
}