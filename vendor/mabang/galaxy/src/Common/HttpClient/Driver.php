<?php

namespace Mabang\Galaxy\Common\HttpClient;

use Mix\ObjectPool\ObjectTrait;
use GuzzleHttp\Client;
/**
 * Class Driver
 * @package Mabang\Galaxy\Common\HttpClient
 */
class Driver
{

    use ObjectTrait;

    /**
     * @var Client
     */
    protected $client;

  
    public function __construct()
    {
        $this->connect();
    }

    /**
     * Get instance
     * @return Client
     */
    public function instance(): Client
    {
        return $this->client;
    }

    /**
     * Connect
     * @throws \Exception
     */
    public function connect()
    {
        $this->client = new Client();
    }

    /**
     * Close
     */
    public function close()
    {
        $this->client = null;
    }

}
