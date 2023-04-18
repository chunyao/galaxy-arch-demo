<?php

namespace Galaxy\Common\Mq;

use App;
use Galaxy\Common\Configur\Cache;
use Galaxy\Core\Log;

use Hyperf\Engine\Http\Client;
use Hyperf\Utils\Coroutine\Concurrent;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class Consumer
{
    private $config;


    public function __construct($config)
    {
        $this->config = $config;


    }

    public function consumeMessage(int $i, $url, AMQPConnection $connect)
    {
        try {
            // 创建通道
            $channel = $connect->getChannel();
            $channel->basic_qos(null, 20, false);

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
            $channel->exchange_declare($exName, 'direct', false, true, false);
            if (isset($this->config['rabbitmq.exchange.dead'][$i])) {
                $channel->exchange_declare($this->config['rabbitmq.exchange.dead'][$i], 'direct', false, true, false);
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

                $deadArgs = new AMQPTable([
                    'x-dead-letter-exchange' => $this->config['rabbitmq.dead.x-dead-letter-exchange'][$i],
                    'x-dead-letter-routing-key' => $this->config['rabbitmq.dead.x-dead-letter-routing-key'][$i],
                    'x-message-ttl' => (int)$this->config['rabbitmq.dead.x-message-ttl'][$i]
                ]);
                $channel->queue_declare($this->config['rabbitmq.queue.dead'][$i], false, true, false, false, false, $deadArgs);
                $args = new AMQPTable([
                    'x-dead-letter-exchange' => $this->config['rabbitmq.exchange.dead'][$i],
                    'x-dead-letter-routing-key' => $this->config['rabbitmq.routekey.dead'][$i]
                ]);
            }
            $queueName = $this->config['rabbitmq.queue'][$i];
            $channel->queue_declare($queueName, false, true, false, false, false, $args);
            // 绑定
            /**
             * $queue           队列名称
             * $exchange        交换机名称
             * $routing_key     路由名称
             */
            $routeKey = $this->config['rabbitmq.routekey'][$i];
            $channel->queue_bind($queueName, $exName, $routeKey);
            if (isset($this->config['rabbitmq.routekey.dead'][$i])) {
                $channel->queue_bind($this->config['rabbitmq.queue.dead'][$i], $this->config['rabbitmq.exchange.dead'][$i], $this->config['rabbitmq.routekey.dead'][$i]);
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

            echo $this->config['rabbitmq.queue'][$i] . " 开始消费" . "Worker 进程ID:" . posix_getpid() . PHP_EOL;
            Log::info($this->config['rabbitmq.queue'][$i] . " 开始消费" . "Worker 进程ID:" . posix_getpid());
            $concurrent = $this->getConcurrent(2);

            $channel->basic_consume($queueName, "", false, false, false, false,
                function (AMQPMessage $msg) use ($concurrent, $i, $url) {
                    $callback = $this->getCallback($i, $url, $msg);
                    if (!$concurrent instanceof Concurrent) {
                        return parallel([$callback]);
                    }
                    $concurrent->create($callback);
                }
            );
            $maxConsumption = 200;
            $currentConsumption = 0;
            //消费
            while ($channel->is_consuming()) {
                //
                $channel->wait(null, true,0);
//            if ($maxConsumption > 0 && ++$currentConsumption >= $maxConsumption) {
//              break;
//            }
                //usleep(300000);
                //   var_dump(memory_get_usage());
            }

        } catch (\Throwable $ex) {
          //  isset($channel) && $channel->close();
            print_r(sprintf('%s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
            throw $ex;
        }
    //    $this->waitConcurrentHandled($concurrent);

    //    $connect->releaseChannel($channel, true);
    }

    /**
     * Wait the tasks in concurrent handled, the max wait time is 5s.
     * @param int $interval The wait interval ms
     * @param int $count The wait count
     */
    protected function waitConcurrentHandled(?Concurrent $concurrent, int $interval = 10, int $count = 500): void
    {
        $index = 0;
        while ($concurrent && !$concurrent->isEmpty()) {
            usleep($interval * 1000);
            if ($index++ > $count) {
                break;
            }
        }
    }

    protected function getConcurrent(int $num): ?Concurrent
    {

        $concurrent = $num;
        if ($concurrent > 1) {
            return new Concurrent($concurrent);
        }

        return null;
    }

    protected function getCallback($i, $req, AMQPMessage $msg)
    {
        return function () use ($i, $req, $msg) {

            /*if (isset($this->config['rabbitmq.qps'][$i])) {
                $sleep = round(1000000 / ((int)$this->config['rabbitmq.qps'][$i]));
                usleep($sleep);
            }*/
            //  sleep(30);
            /*冷启动*/

            $channel = $msg->getChannel();
            $deliveryTag = $msg->getDeliveryTag();

            $tmp = json_decode($msg->body, true);
            $tmp['queue'] = App::$innerConfig['rabbitmq.queue'][$i];

            if (isset($tmp['id'])) {
                $tmp['messageId'] = $tmp['id'];
            }

            if (empty($tmp['messageId'])) {
                Log::error("messageId 为空");
                $channel->basic_ack($deliveryTag);
                return;
            }
            $msgBody['message'] = $tmp;
            $msgBody['messageId'] = $tmp['messageId'];

            $msgBody['queue'] = App::$innerConfig['rabbitmq.queue'][$i];
            $msgBody['type'] = "mq";
            unset($tmp);
            Log::info(sprintf('messageId: %s queue: %s', $msgBody['messageId'], $msgBody['queue']));

            if (Cache::instance()->getIncr($msgBody['messageId']) !== null) {
                if (((int)Cache::instance()->getIncr($msgBody['messageId'])) >= 3) {
                    Log::info(sprintf('重试: ' . Cache::instance()->getIncr($msgBody['messageId']) . ' messageId 丢弃 : %s 进程Id %s', $msgBody['messageId'], posix_getpid()));
                    Cache::instance()->del($msgBody['messageId']);
                    return $channel->basic_reject($deliveryTag, false);
                }
                try {
                    $data = (string)(new \GuzzleHttp\Client())->request('POST',$req, ['timeout' => 120,'json' => $msgBody])->getBody();
                    $resp = json_decode($data);
                    if ($resp->code === 10200) {

                        Log::info(sprintf('messageId ack : %s', $msgBody['messageId']));
                        Cache::instance()->del($msgBody['messageId']);
                        return $channel->basic_ack($deliveryTag);
                    }
                } catch (\Throwable $ex) {
                    Log::error(sprintf('ack: %s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
                }
                Cache::instance()->incr($msgBody['messageId']);
                Log::error(sprintf('重试: ' . Cache::instance()->getIncr($msgBody['messageId']) . ' messageId basic_reject : %s 进程 %s', $msgBody['messageId'], posix_getpid()));

                return $channel->basic_reject($deliveryTag, true);

            } else {
                try {
                    $data = (string)(new \GuzzleHttp\Client())->request('POST',$req, ['timeout' => 120,'json' => $msgBody])->getBody();
                    $resp = json_decode($data);
                    if ($resp->code === 10200) {

                        Log::info(sprintf('messageId ack : %s', $msgBody['messageId']));
                        return $channel->basic_ack($deliveryTag);
                    }
                } catch (\Throwable $ex) {
                    Log::error(sprintf('ack: %s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
                }
                Cache::instance()->setIncr($msgBody['messageId']);

                Log::error(sprintf('重试: ' . $msgBody['messageId'] . ' messageId unack : %s queue: %s', $msgBody['messageId'], $msgBody['queue']));
                return $channel->basic_reject($deliveryTag, true);
            }

        };
    }
}