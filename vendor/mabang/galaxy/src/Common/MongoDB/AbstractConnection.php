<?php

namespace Mabang\Galaxy\Common\MongoDB;

use Mabang\Galaxy\Common\MongoDB\ConnectionInterface;
use Mabang\Galaxy\Common\MongoDB\Driver;
use Mabang\Galaxy\Common\MongoDB\QueryBuilder;
use Mabang\Galaxy\Common\Spl\Exception\Exception;
use Mabang\Galaxy\Core\Log;


use Hyperf\GoTask\MongoClient\Type\BulkWriteResult;
use Hyperf\GoTask\MongoClient\Type\DeleteResult;
use Hyperf\GoTask\MongoClient\Type\InsertOneResult;
use Hyperf\GoTask\MongoClient\Type\UpdateResult;
use Mix\Redis\LoggerInterface;


/**
 * Class AbstractConnection
 * @package Mix\Database
 */
abstract class AbstractConnection implements ConnectionInterface
{
    use QueryBuilder;

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

    private $_limit = 0;
    private $_skip = 0;
    private $_page = 1;
    private $_sort = [];
    private $_fields = [];
    private $_unfields = [];

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
            'Call to undefined method', 'rpc'
        ];
        $errorMessage = $ex->getMessage();
        foreach ($disconnectMessages as $message) {
            if (false !== stripos($errorMessage, $message)) {
                return true;
            }
        }
        return false;
    }

    public function find($where = [], $isArray = true)
    {
        try {
            //指定返回字段
            $fields = array_combine($this->_fields, array_pad([], count($this->_fields), 1));
            $unfields = array_combine($this->_unfields, array_pad([], count($this->_unfields), 0));

            $options = [
                'projection' => array_merge($fields, $unfields),
                'sort' => $this->_sort,
                'limit' => 1, // 指定返回的条数
                'skip' => $this->_skip, // 指定起始位置
            ];
            if (count($options['sort']) == 0) {
                unset($options['sort']);
            }
            $collection = $this->driver->instance()->database($this->database)->collection($this->table)->find($where, $options);
            foreach ($collection as $document) {
                return $this->object2array($document);
            }
            return false;
        } catch (\Throwable $ex) {
            throw new \RuntimeException($ex->getMessage());
        }
    }

    /**
     * 插入数据
     * @param $data
     * @param bool $keepIdColumn
     * @param array $oidArr
     * @return int
     * @throws Exception
     */
    public function insert(array $data, $keepIdColumn = false, &$insertId)
    {
        try {
            if ($keepIdColumn) {
                $data['_id'] = $data[$keepIdColumn] ?? new \MongoDB\BSON\ObjectID;
            }

            $object = $this->driver->instance()->database($this->database)->collection($this->table)->insertOne($data);
            $insertId = (string)$object->getInsertedId();

            return (int)!empty($insertId);
        } catch (\Throwable $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * 批量插入数据
     * @param array $datas
     * @param bool $keepIdColumn
     * @return  int
     * @throws Exception
     */
    public function insertAll(array $datas, $keepIdColumn = false): int
    {
        try {
            $object = $this->driver->instance()->database($this->database)->collection($this->table)->insertMany($datas);
            return count($object->getInsertedIDs());
        } catch (\MongoDB\Driver\Exception\BulkWriteException $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @param array $where = ['x' => ['$gt' => 1]]
     * @param bool $isArray
     * @return array|mixed
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function select($where = [], $isArray = true)
    {

        //指定返回字段
        $fields = array_combine($this->_fields, array_pad([], count($this->_fields), 1));
        $unfields = array_combine($this->_unfields, array_pad([], count($this->_unfields), 0));

        $options = [
            'projection' => array_merge($fields, $unfields),
            'sort' => $this->_sort,
            'limit' => $this->_limit, // 指定返回的条数
            'skip' => $this->_skip, // 指定起始位置
        ];
        if (count($options['sort']) == 0) {
            unset($options['sort']);
        }
        // 查询数据
        $collection = $this->driver->instance()->database($this->database)->collection($this->table)->find($where, $options);

        $count = $this->countDocuments($where);
        $data = [];
        foreach ($collection as $k => $document) {
            $data[$k] = $document;
        }

        if ($isArray) {
            $data = $this->object2array($data);
        }

        $return = [
            'data' => $data,
            'count' => $count,
            'page' => $this->_page,
            'limit' => $this->_limit,
            'skip' => $this->_skip,
        ];
        return $return;
    }

    public function drop()
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->drop();
    }

    public function dropIndexes(array $opts = [])
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->dropIndexes($opts);
    }

    public function dropIndex(string $name, array $opts = [])
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->dropIndex($name, $opts);
    }

    public function listIndexes($indexes = [], array $opts = []): array
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->listIndexes($indexes, $opts);
    }

    public function createIndexes($indexes = [], array $opts = []): array
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->createIndexes($indexes, $opts);
    }

    public function createIndex($index = [], array $opts = []): string
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->createIndex($index, $opts);
    }

    public function distinct(string $fieldName, $filter = [], array $opts = [])
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->distinct($fieldName, $filter, $opts);
    }

    public function bulkWrite($operations = [], array $opts = []): BulkWriteResult
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->bulkWrite($operations, $opts);
    }

    public function aggregate($pipeline = [], array $opts = [])
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->aggregate($pipeline, $opts);
    }

    public function delete($filter = [], array $opts = []): DeleteResult
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->deleteMany($filter, $opts);
    }

    public function deleteMany($filter = [], array $opts = []): DeleteResult
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->deleteMany($filter, $opts);
    }

    public function deleteOne($filter = [], array $opts = []): DeleteResult
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->deleteOne($filter, $opts);
    }

    public function countDocuments($filter = [], array $opts = []): int
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->countDocuments($filter, $opts);
    }

    public function replaceOne($filter = [], $replace = [], array $opts = []): UpdateResult
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->replaceOne($filter, $replace, $opts);
    }

    public function update($filter = [], $update = [], array $opts = []): UpdateResult
    {

        $update = ['$set' => $update];
        $opts = ['multi' => true, 'upsert' => true];
        return $this->driver->instance()->database($this->database)->collection($this->table)->updateMany($filter, $update, $opts);
    }


    public function updateMany($filter = [], $update = [], array $opts = []): UpdateResult
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->updateMany($filter, $update, $opts);
    }

    public function updateOne($filter = [], $update = [], array $opts = []): UpdateResult
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->updateOne($filter, $update, $opts);
    }

    public function findOneAndReplace($filter = [], $replace = [], array $opts = [])
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->findOneAndReplace($filter, $replace, $opts);
    }

    public function findOneAndUpdate($filter = [], $update = [], array $opts = [])
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->findOneAndUpdate($filter, $update, $opts);
    }

    public function findOneAndDelete($filter = [], array $opts = [])
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->findOneAndDelete($filter, $opts);
    }

    public function findOne($filter = [], array $opts = [])
    {
        return $this->driver->instance()->database($this->database)->collection($this->table)->findOne($filter, $opts);
    }


}
