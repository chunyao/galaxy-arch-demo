<?php

namespace Galaxy\Common\MongoDB;


use Galaxy\Common\MongoDB\EmptyDriver;
use Galaxy\Core\Log;
use Hyperf\GoTask\MongoClient\Type\DeleteResult;
use Hyperf\GoTask\MongoClient\Type\UpdateResult;


class Connection extends AbstractConnection
{
    public function find($where = [], $isArray = true)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function insert(array $data, $keepIdColumn = false, &$insertId)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function deleteMany($filter = [], array $opts = []): DeleteResult
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function deleteOne($filter = [], array $opts = []): DeleteResult{
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function replaceOne($filter = [], $replace = [], array $opts = []): UpdateResult{
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function updateMany($filter = [], $update = [], array $opts = []): UpdateResult{
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function updateOne($filter = [], $update = [], array $opts = []): UpdateResult{
        return $this->call(__FUNCTION__, func_get_args());
    }

    protected function call($name, $arguments = [])
    {
        try {
            // 执行父类方法
            return call_user_func_array("parent::{$name}", $arguments);
        } catch (\Throwable $ex) {
            Log::error(['reconnect' => $ex->getMessage(), 'parent' => $name]);
            // 断开连接异常处理
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
        /*  if ($this->inTransaction()) {
              $this->driver->__discard();
              $this->driver = new EmptyDriver();
              return;
          }*/
        $this->driver->__return();
        $this->driver = new EmptyDriver();
    }

}
