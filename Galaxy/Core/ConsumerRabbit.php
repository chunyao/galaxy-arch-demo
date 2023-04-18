<?php

namespace Galaxy\Core;


use App\Config\MQ;
use PhpAmqpLib\Connection\AMQPConnectionConfig;
use PhpAmqpLib\Connection\AMQPConnectionFactory;
use PhpAmqpLib\Wire\AMQPTable;


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

    private function connect(int $vhost)
    {
        $config = new AMQPConnectionConfig();
        if (count($this->config['rabbitmq.host']) > 1) {
            $cur = rand(0, 2);
            $config->setHost($this->config['rabbitmq.host'][$cur]);
            $config->setPort($this->config['rabbitmq.port'][$cur]);
        } elseif (isset($this->config['rabbitmq.host']) && count($this->config['rabbitmq.host']) == 1) {
            $config->setHost($this->config['rabbitmq.host']);
            $config->setPort($this->config['rabbitmq.port']);
        }
        $config->setUser($this->config['rabbitmq.username']);
        $config->setPassword($this->config['rabbitmq.password']);
        $config->setVhost($this->config['rabbitmq.vhost'][$vhost]);
        $config->setInsist(false);
        $config->setLoginMethod('AMQPLAIN');
        $config->setConnectionTimeout(5);
        $config->setLocale('en_US');
        $config->setLoginResponse("");
        $config->setReadTimeout(1800);
        $config->setKeepalive(true);
        $config->setWriteTimeout(1800);
        $config->setHeartbeat(900);
        return AMQPConnectionFactory::create($config);
    }

    public function initQueues($ch, $i)
    {

        try {
            //       $this->con = $this->connect($i);
            $num = 10;
            for ($child = 0; $child < $num; $child++) {
                go(function () use($i){
                    $obj = $this->consumeMessage(1, $i);

                    while ($obj->is_consuming()) {
                        //
                        $obj->wait(null, true);
                        usleep(300000);
                        //   var_dump(memory_get_usage());
                    }
                    $obj->close();
                });

            }



        } catch (\Throwable $ex) {
            Log::error(sprintf('消息队列 %s error %s', $this->config['rabbitmq.queue'][$i], $ex->getMessage()));
            try {
                //  $this->con->close();
            } catch (\Exception $e) {
                Log::error(sprintf('%s in %s on line %d', $e->getMessage(), $e->getFile(), $e->getLine()));
            }

        }

    }

    private function consumeMessage($num, int $i)
    {

        // 创建通道
        $channel[$num] = MQ::instance()->obj();
        $channel[$num]->basic_qos(null, 20, false);
        /**
         * name:xxx             交换机名称
         * type:direct          类型 fanut,direct,topic,headers
         * passive:false        不存在自动创建，如果设置true的话，返回OK，否则失败
         * durable:false        是否持久化
         * auto_delete:false    自动删除，最后一个
         *  exchange_declare(
         * $exchange,
         * $type,
         * $passive = false,
         * $durable = false,
         * $auto_delete = true,
         * $internal = false,
         * $nowait = false,
         * $arguments = array(),
         * $ticket = null
         * )
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
         *
         * $queue = '',
         * $passive = false,
         * $durable = false,
         * $exclusive = false,
         * $auto_delete = true,
         * $nowait = false,
         * $arguments = array(),
         * $ticket = null
         */
        $args = array();
        if (isset($this->config['rabbitmq.queue.dead'][$i])) {
            $deadArgs = array();
            $deadArgs = new AMQPTable([
                'x-dead-letter-exchange' => $this->config['rabbitmq.dead.x-dead-letter-exchange'][$i],
                'x-dead-letter-routing-key' => $this->config['rabbitmq.dead.x-dead-letter-routing-key'][$i],
                'x-message-ttl' => (int)$this->config['rabbitmq.dead.x-message-ttl'][$i]
            ]);
            $channel[$num]->queue_declare($this->config['rabbitmq.queue.dead'][$i], false, true, false, false, false, $deadArgs);
            $args = new AMQPTable([
                'x-dead-letter-exchange' => $this->config['rabbitmq.exchange.dead'][$i],
                'x-dead-letter-routing-key' => $this->config['rabbitmq.routekey.dead'][$i]
            ]);
        }
        $queueName = $this->config['rabbitmq.queue'][$i];
        $channel[$num]->queue_declare($queueName, false, true, false, false, false, $args);
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