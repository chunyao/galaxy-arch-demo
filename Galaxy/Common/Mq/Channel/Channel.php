<?php

namespace Galaxy\Common\Mq\Channel;

use Galaxy\Common\Mq\Channel\Pool\ConnectionPool;
use Galaxy\Common\Mq\Channel\Pool\Dialer;
use Galaxy\Common\Spl\Exception\Exception;
use Mix\Redis\LoggerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;


class Channel
{
    protected $channel;
    protected $ch;
    protected AbstractConnection $connect;
    protected $host;
    protected $port;
    protected $username;
    protected $password;
    protected $vhost;
    protected $driver;

    public $ack = false;

    /**
     * @throws \Exception
     */
    public function __construct($connect)
    {

        $this->connect=$connect;
        $this->driver = new Driver(
            $connect
        );
    }


    public function obj():AMQPChannel
    {

        return $this->borrow()->obj();
    }

    /**
     * @throws Exception
     */
    public function publish($messageBody, $exchange, $routeKey, $head = [], $ack = 0, $retry = 0)
    {
        return $this->borrow()->publish($messageBody, $exchange, $routeKey, $head, $ack, $retry);
    }
    protected function createPool()
    {
        if ($this->driver) {
            $this->driver->close();
            $this->driver = null;
        }

        $this->pool = new ConnectionPool(
            new Dialer(
                $this->connect
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
    protected function borrow(): Connection
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