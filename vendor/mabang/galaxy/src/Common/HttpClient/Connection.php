<?php

namespace Mabang\Galaxy\Common\HttpClient;

/**
 * Class Connection
 * @package Mabang\Galaxy\Common\HttpClient
 */
class Connection extends AbstractConnection
{

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments = [])
    {
        try {
            // 执行父类命令
            return parent::__call($name, $arguments);
        } catch (\Throwable $ex) {
            if (static::isDisconnectException($ex) && !$this->inTransaction()) {
                // 断开连接异常处理
                $this->reconnect();
                // 重新执行命令
                return $this->__call($name, $arguments);
            } else {
                // 丢弃连接
                $this->driver->__discard();
                // 抛出其他异常
                throw $ex;
            }
        }
    }

    public function __destruct()
    {
        if (!$this->driver || $this->driver instanceof EmptyDriver) {
            return;
        }
        $this->driver->__return();
        $this->driver = new EmptyDriver();
    }

}
