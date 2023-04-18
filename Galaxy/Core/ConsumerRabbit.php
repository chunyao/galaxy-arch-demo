<?php

namespace Galaxy\Core;


use App\Config\MQ;
use Galaxy\Common\Mq\Consumer;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Coroutine\Concurrent;
use PhpAmqpLib\Connection\AMQPConnectionConfig;
use PhpAmqpLib\Connection\AMQPConnectionFactory;
use PhpAmqpLib\Message\AMQPMessage;
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


                (new Consumer($this->config))->consumeMessage($i,$this->url);



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




}