<?php

namespace Mabang\Galaxy\Common\Mq;

use Mabang\Galaxy\Core\Log;


class Connection extends AbstractConnection
{

    public function publish($messageBody, $exchange, $routeKey, $head = [], $ack = 0, $retry = 0): int
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @throws \Exception
     */
    protected function call($name, $arguments = [])
    {

        try {
            // 执行父类方法
            return call_user_func_array("parent::{$name}", $arguments);
        } catch (\Throwable $ex) {
            Log::error(['con reconnect' => $ex->getMessage()]);
            if (static::isDisconnectException($ex)) {
                // 断开连接异常处理
                $this->reconnect();
                // 重连后允许再次执行
                // 重新执行方法
                return $this->call($name, $arguments);
            }
        } catch (\Exception $e) {
            Log::error(['reconnect' => $ex->getMessage()]);
        }
    }

    public function __destruct()
    {

        if (!$this->driver || $this->driver instanceof EmptyDriver) {
            return;
        }
//        if ($this->inTransaction()) {
//            $this->driver->__discard();
//            $this->driver = new EmptyDriver();
//            return;
//        }
        $this->driver->__return();
        $this->driver = new EmptyDriver();
    }

}
