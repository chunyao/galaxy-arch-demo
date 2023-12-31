<?php

namespace Mabang\Galaxy\Common\MongoDB;

use Mabang\Galaxy\Core\Log;
use Hyperf\Config\Config;
use Hyperf\GoTask\IPC\SocketIPCSender;
use Hyperf\GoTask\MongoClient\MongoClient;
use Hyperf\GoTask\MongoClient\MongoProxy;
use Mix\ObjectPool\ObjectTrait;

class Driver
{

    use ObjectTrait;


    /**
     * @var array
     */
    protected $options = [];

    protected $mongoDb;
    protected $config;
    protected $task;
    /**
     * Driver constructor.
     * @param array $config
     * @throws \Exception
     */
    //$host, $port, $username, $password, $vhost, $channel
    public function __construct(array $config)
    {

        $this->config = $config;
        $this->connect();
    }

    /**
     * Get instance
     * @return MongoClient
     */
    public function instance(): MongoClient
    {
        return $this->mongoDb;
    }


    /**
     * Connect
     * @throws \Exception
     */
    public function connect()
    {
        //$addr = ROOT_PATH . '/' . md5($this->config['mongo.host'] . $this->config['mongo.user'] . $this->config['mongo.database']) . '.sock';
        $addr = '127.0.0.1:'.$this->config['mongo.pool.port'];
        $this->task = new SocketIPCSender($addr);
        try {
            $this->mongoDb = new MongoClient(new MongoProxy($this->task), new Config([]));
        } catch (\Exception $exception) {
            Log::error(['ex' => $exception]);
        }
    }

    /**
     * Close
     */
    public function close()
    {
        if ($this->mongoDb !== null) {
            unset($this->mongoDb);
        }

    }

}
