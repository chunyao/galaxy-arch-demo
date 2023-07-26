<?php

namespace Mabang\Galaxy\Common\Mq\Channel;

use Mabang\Galaxy\Common\Mq\AMQPConnection;
use Mabang\Galaxy\Core\Log;
use Mix\ObjectPool\ObjectTrait;
use PhpAmqpLib\Channel\AMQPChannel;


class Driver
{
    use ObjectTrait;

    /**
     * @var AMQPChannel
     */
    protected $channel;

    protected AMQPConnection $connect;

    public $size;

    public function __construct($connect)
    {
        $this->connect = $connect;
        $this->channel = $this->connect->getConfirmChannel();
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
            $this->channel = $this->connect->getConfirmChannel();
        } catch (\Exception $exception) {
            $this->reconnect();
            $this->channel = $this->connect->getConfirmChannel();
            $this->size = 0;
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
            $this->connect->releaseChannel($this->channel, true);
            $this->channel = $this->connect->getConfirmChannel();
        } catch (\Exception $exception) {
            $this->connect();
            $this->channel = $this->connect->getConfirmChannel();
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