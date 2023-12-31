<?php

namespace Mabang\Galaxy\Core;

use Mabang\Galaxy\Common\Mq\ConnectionFactory;
use Mabang\Galaxy\Common\Mq\Consumer;
use Hyperf\Amqp\AMQPConnection;
use Hyperf\Utils\Coroutine\Concurrent;
use Mabang\Galaxy\Core\Log;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;


class ConsumerRabbit
{
    private AMQPConnection $con;
    private $config;
    protected $url;

    public function __construct($config, $url)
    {
        $this->config = $config;
        $this->url = $url;

    }

    private function connect(int $vhost)
    {
        if (count($this->config['rabbitmq.host']) > 1) {
            $cur = rand(0, 2);

            $config['host'] = $this->config['rabbitmq.host'][$cur];
            $config['port'] = $this->config['rabbitmq.port'][$cur];
        } elseif (isset($this->config['rabbitmq.host']) && count($this->config['rabbitmq.host']) == 1) {

            $config['host'] = $this->config['rabbitmq.host'];
            $config['port'] = $this->config['rabbitmq.port'];
        }

        $config['user'] = $this->config['rabbitmq.username'];
        $config['password'] = $this->config['rabbitmq.password'];
        $config['vhost'] = $this->config['rabbitmq.vhost'][$vhost];
        $config['params']['read_write_timeout'] = 600;
        $config['params']['channel_rpc_timeout'] = 600;
        $config['params']['heartbeat'] = 300;
        $config['params']['keepalive'] = true;
        $config['params']['connection_timeout'] = 10;
        return (new ConnectionFactory($config))->getConnection('rabbit');
    }

    public function initQueues($ch, $i)
    {
        try {
            $con = $this->connect($i);
            $concurrent = new Concurrent($this->config['rabbitmq.queue.num'][$i]);
            if (isset($this->config['rabbitmq.queue.num'][$i])) {
                for ($k = 0; $k < $this->config['rabbitmq.queue.num'][$i]; $k++) {
                    sleep(3);
                    $concurrent->create(function () use ($i, $con) {
                        (new Consumer($this->config))->consumeMessage($i, $this->url, $con);
                    });
                }
            } else {
                $concurrent->create(function () use ($i, $con) {
                    (new Consumer($this->config))->consumeMessage($i, $this->url, $con);
                });
            }
        } catch (\Throwable $ex) {
            print_r(sprintf('%s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
            Log::error(sprintf('消息队列 %s error %s', $this->config['rabbitmq.queue'][$i], $ex->getMessage()));
            try {
                $this->con->close();
            } catch (\Exception $e) {
                Log::error(sprintf('%s in %s on line %d', $e->getMessage(), $e->getFile(), $e->getLine()));
            }

        }
        return $concurrent;

    }


}