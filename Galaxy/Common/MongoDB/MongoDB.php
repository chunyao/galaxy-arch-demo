<?php

namespace Galaxy\Common\MongoDB;


use Galaxy\Common\MongoDB\Pool\ConnectionPool;
use Galaxy\Common\MongoDB\Pool\Dialer;
use Galaxy\Common\Spl\Exception\Exception;
use Mix\Redis\LoggerInterface;

/**
 * Class Mongo
 * @property ConnectionPool $pool
 * @package Mongo
 */
class MongoDB
{
    private $manager;
    private static $instance = null;
    private $uri;

    private $config = [
        'mongo.host' => "127.0.0.1",
        'mongo.port' => 27017,
        'mongo.user' => "",
        'mongo.password' => "",
        'mongo.database' => "",
        'mongo.replicaset' => "",
    ];


    //
    private $_table = "";
    private $_database = "";
    private $_fields = [];
    private $_unfields = [];
    private $_limit = 0;
    private $_skip = 0;
    private $_page = 1;
    private $_sort = [];
    private $driver ;
    private $pool;
    private $logger;
    private $maxOpen;
    private $maxIdle;
    private $maxLifetime;
    private $waitTimeout;
    public $ack;

    private function reset()
    {
        $this->_table = "";
        $this->_database = "";
        $this->_fields = [];
        $this->_unfields = [];
        $this->_limit = 0;
        $this->_skip = 0;
        $this->_page = 1;
        $this->_sort = [];
    }

    public function __construct($config)
    {
        $this->config = array_merge($this->config, $config);
    }

    public function connect():MongoDB
    {
        $uriOptions = array();

        if ($this->config['mongo.user']) {
            if ($this->config['mongo.replicaset']) {
                $this->uri = sprintf("mongodb://%s:%s@%s/?replicaSet=%s&authSource=%s", $this->config['mongo.user'], $this->config['mongo.password'], $this->config['mongo.host'], $this->config['mongo.replicaset'], $this->config['mongo.database']);

            } else {
                $this->uri = sprintf("mongodb://%s:%s@%s:%d/?authSource=%s", $this->config['mongo.user'], $this->config['mongo.password'], $this->config['mongo.host'], $this->config['mongo.port'], $this->config['mongo.database']);
            }
        } else {
            $this->uri = sprintf("mongodb://%s:%d", $this->config['mongo.host'], $this->config['mongo.port']);
        }
        $uriOptions['socketTimeoutMS'] = 10000;
        $driverOption['disableClientPersistence']=true;
        $this->manager = new \MongoDB\Driver\Manager($this->uri, $uriOptions,$driverOption);
        $cmd = ['ping' => 1];
        try {
           $this->execCommand("admin", $cmd);
        }catch (Exception $e){
            throw new \Exception($e->getMessage());
        }
        $this->_database = $this->config['mongo.database'];
        return $this;
    }

    public function status()
    {
        $rp = new \MongoDB\Driver\ReadPreference('primary');
        $this->manager->selectServer($rp);
        $server = $this->manager->getServers();

        return $server;
    }

    private function __clone()
    {
    }

    public function close()
    {
        unset($this->manager);
        return true;
    }

    /**
     * //设置数据库
     * @param $database
     * @return $this
     */
    public function database($database)
    {
        $this->_database = $database;
        return $this;
    }

    /**
     * 执行mongo cmd 命令
     * @param string $db
     * @param $opts = [
     * 'insert'=>'apps',// collection表名
     * 'documents'=> [
     * [ 'user'=> "abc123", 'status'=> "A" ],
     * [ 'user'=> "abc123", 'status'=> "A" ],
     * ],
     * 'ordered'=>true,
     * 'writeConcern'=>[
     * "w"=>"majority",
     * "wtimeout"=>5000
     * ]
     * ];
     * @return array
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function execCommand($db, $opts)
    {

        $cmd = new \MongoDB\Driver\Command($opts);
        $res = $this->manager->executeCommand($db, $cmd);
        return $res->toArray();
    }

    /**
     * @param $pipeline =
     * [
     * ['$match' => ['age' => 25]], //where 语句
     * ['$group' => ['_id' => '$age',"sum"=>['$sum'=> 1]]], //select count(*) from xx where age = 25 group by age
     * ]
     * @param bool $isArray
     * @return array|mixed
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function aggregate($pipeline, $isArray = true)
    {
        $this->getNameSpace();
        $command = new \MongoDB\Driver\Command([
            'aggregate' => $this->_table,
            'pipeline' => $pipeline,
            'cursor' => new \stdClass,
        ]);
        $cursor = $this->manager->executeCommand($this->_database, $command);
        $this->reset();
        $data = [];
        foreach ($cursor as $k => $document) {
            $data[$k] = $document;
        }
        if ($isArray) {
            $data = $this->object2array($data);
        }
        return $data;
    }

    /**
     * //设置表
     * @param $table
     * @return $this
     */
    public function table($table)
    {
        return $this->borrow()->table_pool($table);
    }

    /**
     * //设置表
     * @param $table
     * @return $this
     */
    public function table_pool($table)
    {
        $this->_database = $this->config['mongo.database'];
        $this->_table = $table;
        return $this;
    }
    public function tableSuffix(string $table, int $companyId, $subTable = 100)
    {
        return $this->borrow()->tableSuffix_pool($table,$companyId,$subTable);

    }
    public function tableSuffix_pool(string $table, int $companyId, $subTable = 100)
    {
        $this->_database = $this->config['mongo.database'];
        $suffix = is_numeric($companyId) ? (int)$companyId % $subTable : null;
        $this->_table = $table . $suffix;
        return $this;
    }
    /**
     * 获取数据库和表组合的命名空间
     * @return string
     * @throws Exception
     */
    private function getNameSpace()
    {

        if ($this->_database && $this->_table) {
            return sprintf("%s.%s", $this->_database, $this->_table);
        }
        throw new \Exception("未设置数据库和表");
    }

    /**
     * 设置返回字段
     * @param array $fields = ["name","age","username"]
     */
    public function field(array $fields)
    {
        $this->_fields = $fields;
        return $this;
    }

    /**
     * 设置屏蔽字段
     * @param array $fields = ["_id"]
     */
    public function unfield(array $fields)
    {
        $this->_unfields = $fields;
        return $this;
    }

    public function limit($limit)
    {
        $this->_limit = $limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->_skip = $offset;
        return $this;
    }

    public function page($page = 1)
    {
        if ($page < 1) {
            $this->_page = 1;
        } else {
            $this->_page = $page;
        }

        $this->_skip = ($this->_page - 1) * $this->_limit;
        return $this;
    }

    public function sort($field, $isAsc = true)
    {
        $s = $isAsc ? 1 : -1;
        $this->_sort[$field] = $s;
        return $this;
    }


    /**
     * 插入数据
     * @param $data
     * @param bool $keepIdColumn
     * @param array $oidArr
     * @return int|null
     * @throws Exception
     */
    public function insert(array $data, $keepIdColumn = false, &$insertId)
    {
        try {
            $bulk = new \MongoDB\Driver\BulkWrite;
            if ($keepIdColumn) {
                $data["_id"] = $data[$keepIdColumn] ?? new \MongoDB\BSON\ObjectID;
            }
            $oidObject = $bulk->insert($data);
            $oidArr = $this->object2array($oidObject);
            if (!$keepIdColumn) {
                $insertId = $oidArr['$oid'];
            } else {
                $insertId = $oidArr;
            }
            $writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000);
            $object = $this->manager->executeBulkWrite($this->getNameSpace(), $bulk, $writeConcern);
            $this->reset();
            return $object->getInsertedCount();
        } catch (\MongoDB\Driver\Exception\BulkWriteException $exception) {
            $this->reset();
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * 批量插入数据
     * @param array $datas
     * @param bool $keepIdColumn
     * @return \MongoDB\Driver\WriteResult
     * @throws Exception
     */
    public function insertAll(array $datas, $keepIdColumn = false)
    {
        try {
            $bulk = new \MongoDB\Driver\BulkWrite;
            foreach ($datas as $data) {
                if ($keepIdColumn) {
                    $data["_id"] = $data[$keepIdColumn] ?? new \MongoDB\BSON\ObjectID;
                }
                $bulk->insert($data);
            }
            $writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000);
            $object = $this->manager->executeBulkWrite($this->getNameSpace(), $bulk, $writeConcern);
            $this->reset();
            return $object->getInsertedCount();
        } catch (\MongoDB\Driver\Exception\BulkWriteException $exception) {
            $this->reset();
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
        // 查询数据
        $query = new \MongoDB\Driver\Query($where, $options);
        $cursor = $this->manager->executeQuery($this->getNameSpace(), $query);

        $count = $this->count($where);
        $data = [];
        foreach ($cursor as $k => $document) {
            $data[$k] = $document;
        }

        if ($isArray) {
            $data = $this->object2array($data);
        }
        $this->reset();
        $return = [
            'data' => $data,
            'count' => $count,
            'page' => $this->_page,
            'limit' => $this->_limit,
            'skip' => $this->_skip,
        ];
        return $return;
    }


    public function count($where = [])
    {
        $command = new \MongoDB\Driver\Command(
            array(
                "count" => $this->_table,
                "query" => $where,
            )
        );
        $result = $this->manager->executeCommand($this->_database, $command)->toArray()[0]->n;
        return $result;
    }


    /**
     * 查找一条记录
     * @param array $where = ['x' => ['$gt' => 1]]
     * @param bool $isArray
     * @return array|mixed
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function find($where = [], $isArray = true)
    {

        //指定返回字段
        $fields = array_combine($this->_fields, array_pad([], count($this->_fields), 1));
        $unfields = array_combine($this->_unfields, array_pad([], count($this->_unfields), 0));

        $options = [
            'projection' => array_merge($fields, $unfields),
            'sort' => $this->_sort,
            'limit' => 1, // 指定返回的条数
            'skip' => $this->_skip, // 指定起始位置
        ];
        // 查询数据
        $query = new \MongoDB\Driver\Query($where, $options);
        $cursor = $this->manager->executeQuery($this->getNameSpace(), $query);
        $this->reset();
        foreach ($cursor as $document) {
            return $this->object2array($document);
        }
        return false;
    }

    /**
     * @param array $where = ['user_id'=>5]
     * @param int $limit 删除数量 0:不限制
     * @param bool $ordered true: 串行执行  false: 并行执行
     * @return \MongoDB\Driver\WriteResult
     * @throws Exception
     */
    public function delete($where = [], $limit = 0, $ordered = true)
    {
        try {
            if ($ordered) {
                $bulk = new \MongoDB\Driver\BulkWrite; //默认是有序的，串行执行
            } else {
                $bulk = new \MongoDB\Driver\BulkWrite(['ordered' => false]);//如果要改成无序操作则加false，并行执行
            }
            // limit 为 1 时，删除第一条匹配数据
            // limit 为 0 时，删除所有匹配数据，默认删除
            $bulk->delete($where, ['limit' => $limit]);//删除user_id为5的字段

            $writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000);
            $object = $this->manager->executeBulkWrite($this->getNameSpace(), $bulk, $writeConcern); //执行写入
            $this->reset();
            return $object->getDeletedCount();
        } catch (\MongoDB\Driver\Exception\BulkWriteException $exception) {
            $this->reset();
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @param array $where = ['user_id' => 2]
     * @param array $update = ['real_name'=>'中国国']
     * @param bool $upsert //没有则插入
     * @param bool $multi //满足条件的都修改
     * @param bool $ordered // 是否无序操作
     */
    public function update(array $where, array $update, $upsert = false, $multi = true, $ordered = true)
    {
        try {
            if ($ordered) {
                $bulk = new \MongoDB\Driver\BulkWrite; //默认是有序的，串行执行
            } else {
                $bulk = new \MongoDB\Driver\BulkWrite(['ordered' => false]);//如果要改成无序操作则加false，并行执行
            }
            $writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000);
            $bulk->update(
                $where,
                ['$set' => $update],
                ['multi' => $multi, 'upsert' => $upsert]
            //multi为true,则满足条件的全部修改,默认为true，如果改为false，则只修改满足条件的第一条
            //upsert为 true：表示不存在就新增
            );

            $object = $this->manager->executeBulkWrite($this->getNameSpace(), $bulk, $writeConcern);
            $this->reset();
            return $object->getModifiedCount();
        } catch (\MongoDB\Driver\Exception\BulkWriteException $exception) {
            $this->reset();
            throw new \Exception($exception->getMessage());
        }
    }

    //对象转数组
    private function object2array($object)
    {
        return json_decode(json_encode($object), true);
    }

    //销毁
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        unset($this->manager);
    }

    /**
     * @throws \Exception
     */
    protected function createPool()
    {
        if ($this->driver) {
            $this->driver->close();
            $this->driver = null;
        }

        $this->pool = new ConnectionPool(
            new Dialer(
                $this->config
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
     * @throws \Exception
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

        if ($this->pool instanceof ConnectionPool) {
            $driver = $this->pool->borrow();
            $conn = new Connection($driver, $this->logger);
        } else {
            $conn = new Connection($this->driver, $this->logger);
        }
        return $conn;
    }
}