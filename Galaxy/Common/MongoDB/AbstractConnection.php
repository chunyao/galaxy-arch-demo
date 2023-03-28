<?php

namespace Galaxy\Common\MongoDB;

use Galaxy\Common\Configur\Cache;
use Galaxy\Common\Spl\Exception\Exception;
use Galaxy\Core\Log;

use Mix\Redis\LoggerInterface;


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
     * 返回当前mongo连接是否在事务内（在事务内的连接回池会造成下次开启事务产生错误）
     * @return bool
     */
//    public function inTransaction(): bool
//    {
//        try {
//            $mongo = $this->driver->instance();
//
//            return $mongo->ack;
//        } catch (\Throwable $e) {
//            Log::error(sprintf('inTransaction %s in %s on line %d', $e->getMessage(), $e->getFile(), $e->getLine()));
//            return false;
//        }
//    }
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
            'Call to undefined method','Missed server heartbeat'
        ];
        $errorMessage = $ex->getMessage();
        foreach ($disconnectMessages as $message) {
            if (false !== stripos($errorMessage, $message)) {
                return true;
            }
        }
        return false;
    }


    public function table_pool($table){
        try {
            $res = $this->driver->instance()->table_pool($table);
        }catch (\Throwable $ex){
            throw new \RuntimeException($ex->getMessage());
        }
        return $res;
    }

    public function tableSuffix_pool(string $table, int $companyId, $subTable = 100){
        try {
            $res = $this->driver->instance()->tableSuffix_pool($table,$companyId,$subTable);
        }catch (\Throwable $ex){
            throw new \RuntimeException($ex->getMessage());
        }
        return $res;
    }





}
