<?php

namespace Galaxy\Core;

use App;
use App\Config\RDS;
use Galaxy\Common\Configur\CoreRDS;
use Galaxy\Core\RobbitMqListener;
use function Swoole\Coroutine\run;
use GuzzleHttp;
use Swoole;

class RabbitMqProcess
{
    private $channel_start = 20;
    private $works = [];
    private $workers = 1;
    private $processes = [];
    private $new_index = 1;
    private $config;
    protected static $httpClient;
    protected $url;

    protected $tcpClient;

    public function __construct($config, $workers, $url, $tcpClient)
    {

        $this->config = $config;
        $this->workers = $workers;
        $this->url = $url;
        $this->tcpClient = $tcpClient;
        self::$httpClient = new GuzzleHttp\Client();
    }

    public function initQueues($ch, $i)
    {
        $step = 60;

        try {
            /*       $host,
               $port,
               $user,
               $password,
               $vhost = '/',
               $insist = false,
               $login_method = 'AMQPLAIN',
               $login_response = null,
               $locale = 'en_US',
               $connection_timeout = 3.0,
               $read_write_timeout = 3.0,
               $context = null,
               $keepalive = false,
               $heartbeat = 0,
               $channel_rpc_timeout = 0.0,
               $ssl_protocol = null,*/
            //
            $params = [
                $this->config['rabbitmq.host'],
                $this->config['rabbitmq.port'],
                $this->config['rabbitmq.username'],
                $this->config['rabbitmq.password'],
                $this->config['rabbitmq.vhost'][$i],
                false,
                "AMQPLAIN", null, 'en_US', 5, 31, null, true, 15
            ];


            // 建立连接
            $conn = new \PhpAmqpLib\Connection\AMQPStreamConnection(...$params);

            // 创建通道
            $channel = $conn->channel($ch);
            $channel->basic_qos(null, 20, null);
            // 创建交换机

            /**
             * name:xxx             交换机名称
             * type:direct          类型 fanut,direct,topic,headers
             * passive:false        不存在自动创建，如果设置true的话，返回OK，否则失败
             * durable:false        是否持久化
             * auto_delete:false    自动删除，最后一个
             */
            $exName = $this->config['rabbitmq.exchange'][$i];
            $channel->exchange_declare($exName, 'direct', false, true, false);
            // 创建队列
            /**
             * name:xxx             队列名称
             * passive:false        不存在自动创建，如果设置true的话，返回OK，否则失败
             * durable:false        是否持久化
             * exclusive:false      是否排他，如果为true的话，只对当前连接有效，连接断开后自动删除
             * auto_delete:false    自动删除，最后一个
             */
            $queueName = $this->config['rabbitmq.queue'][$i];
            $channel->queue_declare($queueName, false, true, false, false);

            // 绑定
            /**
             * $queue           队列名称
             * $exchange        交换机名称
             * $routing_key     路由名称
             */
            $routeKey = $this->config['rabbitmq.routekey'][$i];
            $channel->queue_bind($queueName, $exName, $routeKey);

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

            $callback = function ($msg) use ($i, $msgBody) {
                /*   if (isset($this->config['rabbitmq.qps'][$i])) {
                       $sleep = round(1000000 / ((int)$this->config['rabbitmq.qps'][$i]));
                       usleep($sleep);
                   }
                   /*冷启动*/

                $tmp = json_decode($msg->body, true);
                if (isset($tmp['id']) && empty($tmp['messageId'])){
                    $tmp['messageId'] = $tmp['id'];
                }

                $tmp['queue'] = $this->config['rabbitmq.queue'][$i];
                $msgBody['message'] = $tmp;
                if (isset($tmp['messageId'])) {
                    Log::info(sprintf('messageId: %s', $tmp['messageId']));
                }

                $msgBody['queue'] = $this->config['rabbitmq.queue'][$i];
                $msgBody['type'] = "mq";
                // $resp = json_decode((string)rest_post( $this->url,$msgBody,3));
                try {
                    $data = (string)self::$httpClient->request('POST', $this->url, ['json' => $msgBody])->getBody();
                    $resp = json_decode($data);
                    if ($resp->code === 10200) {
                        $msg->delivery_info["channel"]->basic_ack($msg->delivery_info["delivery_tag"]);
                        if (isset($tmp['messageId'])) {
                            Log::info(sprintf('messageId ack : %s', $tmp['messageId']));
                        }
                    } else {
                        if (isset(APP::$localcache[$tmp['messageId']])) {
                            if (APP::$localcache[$tmp['messageId']] > 3) {
                                Log::error(sprintf('重试: ' . APP::$localcache[$tmp['messageId']] . ' messageId ack : %s', $tmp['messageId']));
                                $msg->delivery_info["channel"]->basicReject($msg->delivery_info["delivery_tag"], false);
                                unset(APP::$localcache[$tmp['messageId']]);
                            }
                            APP::$localcache[$tmp['messageId']]++;

                        } else {
                            APP::$localcache[$tmp['messageId']] = 0;
                                $msg->delivery_info["channel"]->basic_recover(true);
                            Log::error(sprintf('重试: ' . APP::$localcache[$tmp['messageId']] . ' messageId unack : %s', $tmp['messageId']));
                        }
                    }
                } catch (\Throwable $ex) {

                    Log::error(sprintf('ack: %s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));

                }

                // 响应ack
            };
            echo $this->config['rabbitmq.queue'][$i] . " 开始消费\n";
            Log::info($this->config['rabbitmq.queue'][$i] . " 开始消费");
            $return = $channel->basic_consume($queueName, "", false, false, false, false, $callback);
            // 监听
            while ($channel->is_consuming()) {
                $channel->wait();
            }
            $channel->close();
            $conn->close();
        } catch (\Throwable $ex) {
            Log::error(sprintf('%s error', $this->config['rabbitmq.queue'][$i]));
            Log::error(sprintf('%s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));

        }

    }

    public function createProcess($index = null, $ch, $queue)
    {

        $process = new Swoole\Process(function ($worker) use ($index, $ch, $queue) {
            while (1) {
                sleep(5);
                log::info("消息进程ID:" . posix_getpid() . "\n");
                $this->initQueues($ch, $queue);
            }
        }, false, 0, true);

        $pid = $process->start();
        $this->works[$index] = $pid;
        $this->processes[$pid] = $process;
        echo "Mq Master: new worker, PID=" . $pid . "\n";
        return $pid;
    }

    public function handler()
    {
        $channel_step = 0;
        for ($worker = 0; $worker < $this->workers; $worker++) {

            $i = 0;
            foreach ($this->config['rabbitmq.enable'] as $key => $val) {

                if ($val) {

                    foreach (RobbitMqListener::rabbitQueueload($this->config['app.name']) as $key => $val) {

                        if ($val::getQueue() == $this->config['rabbitmq.queue'][$i]) $this->createProcess($worker, $this->channel_start + $channel_step, $i);
                    }
                }
                $i++;
                $channel_step++;
            }
        }
    }
}
