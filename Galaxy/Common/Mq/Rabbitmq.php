<?php

namespace Galaxy\Common\Mq;

use Galaxy\Core\Log;
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
    private $i = 0;

    public function __construct($host, $port, $username, $password, $vhost, $channel)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->vhost = $vhost;
        $this->ch = $channel;
        $this->connect();

        $this->channel = $this->con->channel($this->ch);
    }

    private function connect()
    {
        if (isset($this->host[1])) {
            var_dump( $this->port[0]);
            $this->con = new AMQPStreamConnection($this->host[0], $this->port[0], $this->username, $this->password, $this->vhost, false, 'AMQPLAIN', null, 'en_US', 3, 21, null, false, 10);
        } else {
            $this->con = new AMQPStreamConnection($this->host, $this->port, $this->username, $this->password, $this->vhost, false, 'AMQPLAIN', null, 'en_US', 3, 21, null, false, 10);

        }
        swoole_timer_tick(10000, function () {
            try {
                $this->con->checkHeartBeat();
            } catch (\Throwable $e) {
                //var_dump($e);
            }
        });
    }

    /**
     * @param $messageBody  消息内容
     * @param $exchange     交换机名
     * @param $routeKey     路由键
     * @param array $head
     * @return bool
     */
    public function publish($messageBody, $exchange, $routeKey, $head = [], $ack = 0, $retry = 0)
    {
        $status = 0;
        $this->i = $retry;
        if (empty($this->channel)) {
            $this->connect();
        }
        try {
            $this->channel = $this->con->channel(rand(0, 1000));
            $head = array_merge(array('content_type' => 'text/json', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT), $head);
            $message = new AMQPMessage($messageBody, $head);
            //推送成功
            if ($ack === 1) {
                $this->channel->set_ack_handler(
                    function (AMQPMessage $message) use (&$status) {
                        $status = 1;
                        //  echo "发送成功: " . $message->body . PHP_EOL;
                    }
                );

                //推送失败
                $this->channel->set_nack_handler(
                    function (AMQPMessage $message) use (&$status) {
                        $status = 2;
                        //   echo "发送失败: " . $message->body . PHP_EOL;
                    }
                );
                $this->channel->confirm_select();
            }

            $this->channel->basic_publish($message, $exchange, $routeKey);
            if ($ack === 1) {
                $this->channel->wait_for_pending_acks();
                $this->channel->close();
            }
            unset($message);
        } catch (\Throwable $ex) {
            Log::error(sprintf('message publish: %s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
            if ($this->i < 3) {
                $this->con->close();
                $this->connect();
                $status = $this->publish($messageBody, $exchange, $routeKey, $head, $ack, $this->i++);
                Log::error(sprintf('message publish retry: ' . $this->i . ' status %s ', $status));
            }


        }


        // 响应ack
        unset($messageBody);
        return $status;
    }

    public function __destruct()
    {
        try {
            Log::info(sprintf('connect close'));
            $this->con->close();
        } catch (\Throwable $ex) {
            Log::error(sprintf('%s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
        }

    }
}