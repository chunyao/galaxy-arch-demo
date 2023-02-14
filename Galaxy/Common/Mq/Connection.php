<?php
namespace Galaxy\Common\Mq;


class Connection extends AbstractConnection
{

    protected function call($name, $arguments = [])
    {
        try {
            // 执行父类方法
            return call_user_func_array("parent::{$name}", $arguments);
        } catch (\Throwable $ex) {
            if (static::isDisconnectException($ex)) {
                // 断开连接异常处理
                $this->reconnect();
                // 重连后允许再次执行
                // 重新执行方法
                return $this->call($name, $arguments);
            }
        }
    }

    public function __destruct()
    {
        if (!$this->driver || $this->driver instanceof EmptyDriver) {
            return;
        }

        $this->driver->instance()->close();
        $this->driver = new EmptyDriver();
    }

}
