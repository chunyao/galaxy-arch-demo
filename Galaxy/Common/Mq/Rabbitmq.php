<?php

namespace Galaxy\Common\Mq;


use Galaxy\Common\Mq\Pool\ConnectionPool;
use Galaxy\Common\Mq\Pool\Dialer;
use Galaxy\Common\Spl\Exception\Exception;
use Galaxy\Core\Log;
use Galaxy\Core\Once;
use Mix\Redis\LoggerInterface;
use PhpAmqpLib\Message\AMQPMessage;


class Rabbitmq
{
    protected static $channel;
    protected static $once;
    protected $ch;
    protected $host;
    protected $port;
    protected $username;
    protected $password;
    protected $vhost;
    private $i = 0;
    public  $driver;

    public $ack = false;

    /**
     * @throws \Exception
     */
    public function __construct($host, $port, $username, $password, $vhost, $channel)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->vhost = $vhost;
        $this->ch = $channel;

        $this->driver = new Driver(
            $this->host,
            $this->port,
            $this->username,
            $this->password,
            $this->vhost,
            $this->ch,
        );
    }


    /**
     * @throws Exception
     */
    public function publish($messageBody, $exchange, $routeKey, $head = [], $ack = 0, $gzip = 0)
    {
        $status = 0;
        try {
            $header= array_merge(array('content_type' => 'application/json', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT), $head);
            if ($gzip){
                $messageBody=gzcompress($messageBody,9);
                $header = array_merge(array('content_type' => 'application/gzip', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT), $head);
            }
            $message = new AMQPMessage($messageBody, $header);
            //推送成功
            $channel =  $this->driver->con->getChannel();
            if ($ack === 1) {
                $this->ack = true;
                $channel->set_ack_handler(
                    function (AMQPMessage $message) use (&$status) {
                        $status = 1;
                        //    echo "发送成功: " . $message->body . PHP_EOL;
                    }
                );

                $channel->confirm_select();
            }
            $channel->basic_publish($message, $exchange, $routeKey,true);

            if ($ack === 1) {
                $channel->wait_for_pending_acks_returns();
                $this->driver->con->releaseChannel($channel,true);
                //  $this->driver->close();
                //   $this->driver->reconnect();
            }
            $this->ack = false;
            //    unset($message);
        } catch (\Throwable $ex) {
            Log::error(sprintf('message publish: %s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
            throw new Exception($ex);
        }
        // 响应ack
        //  unset($messageBody);
        //
        return $status;
    }


    protected function createPool()
    {
        if ($this->driver) {
            $this->driver->closeCon();
            $this->driver = null;
        }

        $this->pool = new ConnectionPool(
            new Dialer(
                $this->host,
                $this->port,
                $this->username,
                $this->password,
                $this->vhost,
                $this->ch,
            ),
            $this->maxOpen,
            $this->maxIdle,
            $this->maxLifetime,
            $this->waitTimeout
        );

    }



    /**
     * @param int $maxOpen
     * @param int $maxIdle
     * @param int $maxLifetime
     * @param float $waitTimeout
     */
    public function startPool(int $maxOpen, int $maxIdle, int $maxLifetime = 0, float $waitTimeout = 0.0)
    {
        $this->maxOpen = $maxOpen;
        $this->maxIdle = $maxIdle;
        $this->maxLifetime = $maxLifetime;
        $this->waitTimeout = $waitTimeout;
        $this->createPool();
    }

    /**
     * @param int $maxOpen
     */
    public function setMaxOpenConns(int $maxOpen)
    {
        if ($this->maxOpen == $maxOpen) {
            return;
        }
        $this->maxOpen = $maxOpen;
        $this->createPool();
    }

    /**
     * @param int $maxIdle
     */
    public function setMaxIdleConns(int $maxIdle)
    {
        if ($this->maxIdle == $maxIdle) {
            return;
        }
        $this->maxIdle = $maxIdle;
        $this->createPool();
    }

    /**
     * @param int $maxLifetime
     */
    public function setConnMaxLifetime(int $maxLifetime)
    {
        if ($this->maxLifetime == $maxLifetime) {
            return;
        }
        $this->maxLifetime = $maxLifetime;
        $this->createPool();
    }

    /**
     * @param float $waitTimeout
     */
    public function setPoolWaitTimeout(float $waitTimeout)
    {
        if ($this->waitTimeout == $waitTimeout) {
            return;
        }
        $this->waitTimeout = $waitTimeout;
        $this->createPool();
    }

    /**
     * @return array
     */
    public function poolStats(): array
    {
        if (!$this->pool) {
            return [];
        }
        return $this->pool->stats();
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Borrow connection
     * @return Connection
     * @throws WaitTimeoutException
     */
    public function borrow(): Connection
    {
        if ($this->pool instanceof ConnectionPool) {
            $driver = $this->pool->borrow();
            $conn = new Connection($driver);
        } else {
            $conn = new Connection($this->driver);

        }
        return $conn;
    }
}