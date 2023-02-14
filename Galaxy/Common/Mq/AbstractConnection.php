<?php

namespace Galaxy\Common\Mq;

use Galaxy\Common\Configur\Cache;
use Galaxy\Common\Spl\Exception\Exception;
use Galaxy\Core\Log;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractConnection
 * @package Mix\Database
 */
abstract class AbstractConnection implements ConnectionInterface
{


    /**
     * 驱动
     * @var Driver
     */
    protected $driver;

    /**
     * @var LoggerInterface
     */
    protected $logger;



    /**
     * values
     * @var array
     */
    protected $values = [];


    /**
     * 归还连接前缓存处理
     * @var array
     */
    protected $options = [];

    /**
     * 归还连接前缓存处理
     * @var string
     */
    protected $lastInsertId;

    /**
     * 归还连接前缓存处理
     * @var int
     */
    protected $rowCount;

    /**
     * 因为协程模式下每次执行完，Driver 会被回收，因此不允许复用 Connection，必须每次都从 Database->borrow()
     * 为了保持与同步模式的兼容性，因此限制 Connection 不可多次执行
     * 事务在 commit rollback __destruct 之前可以多次执行
     * @var bool
     */
    protected $executed = false;

    /**
     * AbstractConnection constructor.
     * @param Driver $driver
     * @param LoggerInterface|null $logger
     */
    public function __construct(Driver $driver, ?LoggerInterface $logger)
    {
        $this->driver = $driver;
        $this->logger = $logger;

    }

    /**
     * 连接
     * @throws \Exception
     */
    public function connect(): void
    {
        $this->driver->connect();
    }

    /**
     * 关闭连接
     */
    public function close(): void
    {
        $this->driver->close();
    }

    /**
     * 重新连接
     * @throws \Exception
     */
    protected function reconnect(): void
    {
        $this->close();
        $this->connect();
    }

    /**
     * 判断是否为断开连接异常
     * @param \Throwable $ex
     * @return bool
     */
    protected static function isDisconnectException(\Throwable $ex)
    {
        $disconnectMessages = [
            'Call to undefined method',
        ];
        $errorMessage = $ex->getMessage();
        foreach ($disconnectMessages as $message) {
            if (false !== stripos($errorMessage, $message)) {
                return true;
            }
        }
        return false;
    }


    public function publish($messageBody, $exchange, $routeKey, $head = [], $ack = 0, $retry = 0): ConnectionInterface
    {

        // 执行
        return $this->driver->instance()->publish($messageBody, $exchange, $routeKey, $head , $ack, $retry);
    }



}
