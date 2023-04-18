<?php

namespace Galaxy\Common\Mq\Channel;



use PhpAmqpLib\Channel\AMQPChannel;

class EmptyDriver
{

    protected $errorMessage = 'The connection has been returned to the pool, the current operation cannot be performed';

    public function __construct()
    {

    }

    public function instance(): AMQPChannel
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
