<?php

namespace  Galaxy\Common\Mq\Channel;

use Galaxy\Core\Log;
use Mix\ObjectPool\ObjectTrait;
use PhpAmqpLib\Channel\AMQPChannel;


class Driver
{
    use ObjectTrait;

    /**
     * @var AMQPChannel
     */
    protected $channel;

    protected $connect;

    public $size;

    public function __construct($connect)
    {
        $this->connect = $connect;
        $this->channel =$this->connect->channel();
        $this->connect();
    }

    /**
     * Get instance
     * @return AMQPChannel
     */
    public function instance(): AMQPChannel
    {
        return $this->channel;
    }

    /**
     * Connect
     * @throws \Exception
     */
    public function connect()
    {

        try {
            $this->channel = $this->connect->channel();
        } catch (\Exception $exception) {
            $this->reconnect();
            $this->channel = $this->connect->channel();
            $this->size=0;
            Log::error(['channel reconnect' => $exception->getMessage()]);
        }
    }
    /**
     * Connect
     * @throws \Exception
     */
    public function reconnect()
    {

        try {
            $this->channel->close();
            $this->channel = $this->connect->channel();
        } catch (\Exception $exception) {
            $this->connect();
            $this->channel = $this->connect->channel();
            Log::error(['reconnect' => $exception->getMessage()]);
        }
    }
    /**
     * Close
     */
    public function close()
    {

        if ($this->channel !== null) {
            $this->channel->close();
        }

    }
}