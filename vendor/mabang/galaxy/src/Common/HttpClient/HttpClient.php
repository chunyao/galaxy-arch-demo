<?php

namespace Mabang\Galaxy\Common\HttpClient;

use Mix\ObjectPool\Exception\WaitTimeoutException;
use Mabang\Galaxy\Common\HttpClient\Pool\ConnectionPool;
use Mabang\Galaxy\Common\HttpClient\Pool\Dialer;
use Swoole\Runtime;

/**
 * Class Redis
 * @package Mix\Redis
 */
class HttpClient implements ConnectionInterface
{


    /**
     * 连接池
     * @var ConnectionPool
     */
    protected $pool;

    /**
     * @var Driver
     */
    protected $driver;

    /**
     * @var LoggerInterface
     */
    protected $logger;


    public function __construct()
    {


        $this->driver = new Driver(
            $this->host,
            $this->port,
            $this->password,
            $this->database,
            $this->timeout,
            $this->retryInterval,
            $this->readTimeout
        );
    }

    protected function createPool()
    {
        if ($this->driver) {
            $this->driver->close();
            $this->driver = null;
        }

        $this->pool = new ConnectionPool(
            new Dialer(),
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
        if ($this->pool) {
            $driver = $this->pool->borrow();
            $conn = new Connection($driver, $this->logger);
        } else {
            $conn = new Connection($this->driver, $this->logger);
        }
        return $conn;
    }

    /**
     * Call
     * @param $command
     * @param $arguments
     * @return mixed
     * @throws \RedisException
     */
    public function __call($command, $arguments)
    {
        Runtime::enableCoroutine(SWOOLE_HOOK_NATIVE_CURL);
        //   $call[] = function () use ($command, $arguments) {
        return $this->borrow()->__call($command, $arguments);
        //    };
        //   return  parallel($call,1)[0];
    }

}
