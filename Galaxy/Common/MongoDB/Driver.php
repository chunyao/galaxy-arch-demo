<?php

namespace Galaxy\Common\MongoDB;

use Mix\ObjectPool\ObjectTrait;

/**
 * Class Driver
 * @package Mix\Redis
 */
class Driver
{

    use ObjectTrait;

    /**
     * @var string
     */
    protected $host = '';

    /**
     * @var int
     */
    protected $port = 6379;

    /**
     * @var string
     */
    protected $password = '';

    /**
     * @var int
     */
    protected $database = 0;

    /**
     * @var float
     */
    protected $timeout = 5.0;

    /**
     * @var int
     */
    protected $retryInterval = 0;

    /**
     * 读取超时
     * phpredis >= 3.1.3
     * @var int
     */
    protected $readTimeout = -1;

    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * Driver constructor.
     * @param string $host
     * @param int $port
     * @param string $password
     * @param int $database
     * @param float $timeout
     * @param int $retryInterval
     * @param int $readTimeout
     * @throws \RedisException
     */
    public function __construct(string $host, int $port = 6379, string $password = '', int $database = 0, float $timeout = 5.0, int $retryInterval = 0, int $readTimeout = -1)
    {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->database = $database;
        $this->timeout = $timeout;
        $this->retryInterval = $retryInterval;
        $this->readTimeout = $readTimeout;
        $this->connect();
    }

    /**
     * Get instance
     * @return \Mongo
     */
    public function instance()
    {
        //检测当前类属性$instance是否已经保存了当前类的实例
        if (self::$instance == null) {
            //如果没有,则创建当前类的实例
            self::$instance = new self($config);
        }
        //如果已经有了当前类实例,就直接返回,不要重复创建类实例
        return self::$instance;
    }

    /**
     * Connect
     * @throws \MongoConnectionException
     */
    public function connect()
    {
        $options = ["connect" => true];
        $manager = new \MongoClient('mongodb://jiagou:mtViTGogNLH2iwg@192.168.2.20:27017,192.168.2.21:27017/?replicaSet=mongos&authSource=admin', $options);

        $this->mongo = $manager;
    }

    /**
     * Close
     */
    public function close()
    {
        $this->mongo and $this->mongo->close();
    }

}
