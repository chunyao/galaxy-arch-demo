<?php

namespace Galaxy\Common\Mq;

use Galaxy\Common\Mq\Pool\ConnectionPool;
use Galaxy\Common\Mq\Pool\Dialer;
use Galaxy\Core\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Swoole;

class Rabbitmq
{
    protected $channel;
    protected $ch;
    protected $con;
    protected $host;
    protected $port;
    protected $username;
    protected $password;
    protected $vhost;
    private $i = 0;
    protected $driver;

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

    public function connect()
    {
        if (isset($this->host[1])) {
            $i = rand(0, 2);
            $this->con = new AMQPStreamConnection($this->host[$i], $this->port[$i], $this->username, $this->password, $this->vhost, false, 'AMQPLAIN', null, 'en_US', 10, 60, null, false, 30);
        } else {
            $this->con = new AMQPStreamConnection($this->host, $this->port, $this->username, $this->password, $this->vhost, false, 'AMQPLAIN', null, 'en_US', 3, 60, null, false, 30);

        }
        $this->channel = $this->con->channel($this->ch);
        swoole_timer_tick(30000, function () {
            try {
                $this->con->checkHeartBeat();
            } catch (\Throwable $e) {
                //var_dump($e);
            }
        });
    }

    /**
     * @param $messageBody  消息内容
     * @param $exchange     交换机名
     * @param $routeKey     路由键
     * @param array $head
     * @return bool
     */
    public function publish($messageBody, $exchange, $routeKey, $head = [], $ack = 0, $retry = 0): int
    {
        $status = 0;
        $this->i = $retry;
        if (empty($this->channel)) {
            $this->connect();
        }
        try {
            $this->channel = $this->con->channel(rand(0, 1000));
            $head = array_merge(array('content_type' => 'text/json', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT), $head);
            $message = new AMQPMessage($messageBody, $head);
            //推送成功
            if ($ack === 1) {
                $this->channel->set_ack_handler(
                    function (AMQPMessage $message) use (&$status) {
                        $status = 1;
                        //  echo "发送成功: " . $message->body . PHP_EOL;
                    }
                );

                //推送失败
                $this->channel->set_nack_handler(
                    function (AMQPMessage $message) use (&$status) {
                        $status = 2;
                        //   echo "发送失败: " . $message->body . PHP_EOL;
                    }
                );
                $this->channel->confirm_select();
            }

            $this->channel->basic_publish($message, $exchange, $routeKey);
            if ($ack === 1) {
                $this->channel->wait_for_pending_acks();
                $this->channel->close();
            }
            unset($message);
        } catch (\Throwable $ex) {
            Log::error(sprintf('message publish: %s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
            if ($this->i < 3) {
                $this->channel->close();
                $this->con->close();
                $this->connect();
                $status = $this->publish($messageBody, $exchange, $routeKey, $head, $ack, $this->i++);
                Log::error(sprintf('message publish retry: ' . $this->i . ' status %s ', $status));
            }


        }


        // 响应ack
        unset($messageBody);
        return $status;
    }

    public function close()
    {
        try {
            Log::info(sprintf('connect close'));
            $this->channel->close();
            $this->con->close();
        } catch (\Throwable $ex) {
            Log::error(sprintf('%s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
        }

    }

    public function __destruct()
    {
        try {
            Log::info(sprintf('connect close'));
            $this->con->close();
        } catch (\Throwable $ex) {
            Log::error(sprintf('%s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
        }

    }

    protected function createPool()
    {
        if ($this->driver) {
            $this->driver->close();
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
}