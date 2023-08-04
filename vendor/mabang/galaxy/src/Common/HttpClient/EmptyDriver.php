<?php

namespace Mabang\Galaxy\Common\HttpClient;

/**
 * Class EmptyDriver
 * @package Mabang\Galaxy\Common\HttpClient
 */
class EmptyDriver
{

    protected $errorMessage = 'The connection has been returned to the pool, the current operation cannot be performed';

    public function __construct()
    {
    }

    public function instance(): \Redis
    {
        throw new \RuntimeException($this->errorMessage);
    }

    public function connect()
    {
        throw new \RuntimeException($this->errorMessage);
    }

    public function close()
    {
        throw new \RuntimeException($this->errorMessage);
    }

}
