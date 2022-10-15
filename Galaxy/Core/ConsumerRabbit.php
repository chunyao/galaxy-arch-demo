<?php

namespace Galaxy\Core;

use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;


class ConsumerRabbit
{
    private $con;
    private $config;
    protected $url;

    public function __construct($config, $url)
    {

        $this->config = $config;
        $this->url = $url;

    }

    public function initQueues($ch, $i)
    {
        $up = 2;
        $baseMemory = memory_get_usage();

        try {
            if (isset($this->config['rabbitmq.host'][1])) {
                $this->con = AMQPSocketConnection::create_connection([
                        ['host' => $this->config['rabbitmq.host'][0], 'port' => $this->config['rabbitmq.port'][0], 'user' => $this->config['rabbitmq.username'], 'password' => $this->config['rabbitmq.password'], 'vhost' => $this->config['rabbitmq.vhost'][0]],
                        ['host' => $this->config['rabbitmq.host'][1], 'port' => $this->config['rabbitmq.port'][1], 'user' => $this->config['rabbitmq.username'], 'password' => $this->config['rabbitmq.password'], 'vhost' => $this->config['rabbitmq.vhost'][0]],
                        ['host' => $this->config['rabbitmq.host'][2], 'port' => $this->config['rabbitmq.port'][2], 'user' => $this->config['rabbitmq.username'], 'password' => $this->config['rabbitmq.password'], 'vhost' => $this->config['rabbitmq.vhost'][0]],
                    ]
                    , [
                        'insist' => false,
                        'login_method' => 'AMQPLAIN',
                        'login_response' => null,
                        'connection_timeout' => 5,
                        'locale' => 'en_US',
                        'read_timeout' => 180,
                        'keepalive' => true,
                        'write_timeout' => 180,
                        'heartbeat' => 90
                    ]);
            } else {
                $params = [
                    $this->config['rabbitmq.host'],
                    $this->config['rabbitmq.port'],
                    $this->config['rabbitmq.username'],
                    $this->config['rabbitmq.password'],
                    $this->config['rabbitmq.vhost'][$i],
                    false,
                    "AMQPLAIN", null, 'en_US', 5, 60, null, true, 30
                ];


                // 建立连接
                $this->con = new AMQPStreamConnection(...$params);
            }
            $obj = $this->consumeMessage(0, $i);
            while ($obj->is_consuming()) {
                //  usleep(50000);
                $obj->wait(null, true);
                //   var_dump(memory_get_usage());
            }
            $obj->close();


        } catch (\Throwable $ex) {
            Log::error(sprintf('消息队列 %s error', $this->config['rabbitmq.queue'][$i]));
            try {
                $this->con->close();
            } catch (\Exception $e) {
                Log::error(sprintf('%s in %s on line %d', $e->getMessage(), $e->getFile(), $e->getLine()));
            }

        }

    }

    private function consumeMessage(int $num, int $i)
    {
        $c = rand(0, 2000);
        // 创建通道
        $channel[$num] = $this->con->channel($c);
        $channel[$num]->basic_qos(null, 20, false);
        /**
         * name:xxx             交换机名称
         * type:direct          类型 fanut,direct,topic,headers
         * passive:false        不存在自动创建，如果设置true的话，返回OK，否则失败
         * durable:false        是否持久化
         * auto_delete:false    自动删除，最后一个
         */
        $exName = $this->config['rabbitmq.exchange'][$i];
        $channel[$num]->exchange_declare($exName, 'direct', false, true, false);
        if (isset($this->config['rabbitmq.exchange.dead'][$i])) {
            $channel[$num]->exchange_declare($this->config['rabbitmq.exchange.dead'][$i], 'direct', false, true, false);
        }

        // 创建队列
        /**
         * name:xxx             队列名称
         * passive:false        不存在自动创建，如果设置true的话，返回OK，否则失败
         * durable:false        是否持久化
         * exclusive:false      是否排他，如果为true的话，只对当前连接有效，连接断开后自动删除
         * auto_delete:false    自动删除，最后一个
         */
        $queueName = $this->config['rabbitmq.queue'][$i];
        $channel[$num]->queue_declare($queueName, true, true, false, false);
        if (isset($this->config['rabbitmq.queue.dead'][$i])) {
            $channel[$num]->queue_declare($this->config['rabbitmq.queue.dead'][$i], false, true, false, false);
        }
        // 绑定
        /**
         * $queue           队列名称
         * $exchange        交换机名称
         * $routing_key     路由名称
         */
        $routeKey = $this->config['rabbitmq.routekey'][$i];
        $channel[$num]->queue_bind($queueName, $exName, $routeKey);
        if (isset($this->config['rabbitmq.routekey.dead'][$i])) {
            $channel[$num]->queue_bind($this->config['rabbitmq.queue.dead'][$i], $this->config['rabbitmq.exchange.dead'][$i], $this->config['rabbitmq.routekey.dead'][$i]);
        }
        // 消费
        /**
         * $queue = '',         被消费队列名称
         * $consumer_tag = '',  消费者客户端标识，用于区分客户端
         * $no_local = false,   这个功能属于amqp的标准，但是rabbitmq未实现
         * $no_ack = false,     收到消息后，是否要ack应答才算被消费
         * $exclusive = false,  是否排他，即为这个队列只能由一个消费者消费，适用于任务不允许并发处理
         * $nowait = false,     不返回直接结果，但是如果排他开启的话，则必须需要等待结果的，如果二个都开启会报错
         * $callback = null,    回调函数处理逻辑
         */
        // 回调
        $msgBody = array();
        $req = $this->url;
        $callback[$num] = require __DIR__ . '/RabbitMqMsg.php';
        echo $this->config['rabbitmq.queue'][$i] . " 开始消费" . "Worker 进程ID:" . posix_getpid() . PHP_EOL;
        Log::info($this->config['rabbitmq.queue'][$i] . " 开始消费" . "Worker 进程ID:" . posix_getpid());
        $channel[$num]->basic_consume($queueName, "", false, false, false, false, $callback[$num]);
        //消费
        return $channel[$num];

    }


}