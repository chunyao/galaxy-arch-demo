<?php

namespace Galaxy\Common\MongoDB;

use Galaxy\Core\Log;
use Mix\ObjectPool\ObjectTrait;

class Driver
{

    use ObjectTrait;


    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var MongoDB
     */
    protected $mongoDb;
    protected $config;

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
     * @return MongoDB
     */
    public function instance(): MongoDB
    {
        return $this->mongoDb;
    }


    /**
     * Connect
     * @throws \Exception
     */
    public function connect()
    {
        try {
            $this->mongoDb = (new MongoDB($this->config))->connect();
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
            $this->mongoDb->close();
        }

    }

}
