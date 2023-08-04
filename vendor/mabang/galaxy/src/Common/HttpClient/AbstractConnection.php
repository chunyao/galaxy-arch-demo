<?php
namespace Mabang\Galaxy\Common\HttpClient;

/**
 * Class AbstractConnection
 * @package Mabang\Galaxy\Common\HttpClient
 */
abstract class AbstractConnection implements ConnectionInterface
{
    

    /**
     * @var Driver
     */
    protected $driver;

    /**
     * @var LoggerInterface|null
     */
    protected $logger;

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
    public function connect()
    {
        $this->driver->connect();
    }

    /**
     * 关闭连接
     */
    public function close()
    {
        $this->driver->close();
    }

    /**
     * 重新连接
     * @throws \Exception
     */
    protected function reconnect()
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
            'went away',
            'failed with errno',
            'connection lost',
        ];
        $errorMessage = $ex->getMessage();
        foreach ($disconnectMessages as $message) {
            if (false !== stripos($errorMessage, $message)) {
                return true;
            }
        }
        return false;
    }
    

    /**
     * 执行命令
     * @param string $command
     * @param array $arguments
     * @return mixed
     * @throws \Throwable
     */
    public function __call(string $command, array $arguments = [])
    {
        $beginTime = microtime(true);

        try {
            $result = call_user_func_array([$this->driver->instance(), $command], $arguments);
        } catch (\Throwable $ex) {
            throw $ex;
        } finally {
            // 记录执行时间
            $time = round((microtime(true) - $beginTime) * 1000, 2);

            // logger
            if ($this->logger) {
                $this->logger->trace(
                    $time,
                    $command,
                    $arguments,
                    $ex ?? null
                );
            }
        }

        return $result;
    }

}
