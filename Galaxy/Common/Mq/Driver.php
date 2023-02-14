<?php

namespace  Galaxy\Common\Mq;

use Mix\ObjectPool\ObjectTrait;

/**
 * Class Driver
 * @package Mix\Database
 */
class Driver
{

    use ObjectTrait;

    /**
     * @var array
     */
    protected $host = '';

    /**
     * @var string
     */
    protected $username = 'root';

    /**
     * @var string
     */
    protected $password = '';

    /**
     * @var array
     */
    protected $port = 5672;

    /**
     * @var string
     */
    protected $vhost = '';

    /**
     * @var int
     */
    protected $channel ;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var Rabbitmq
     */
    protected $rabbitmq;


    /**
     * Driver constructor.
     * @param array $hosts
     * @param array $port
     * @param string $username
     * @param string $password
     * @param string $vhost
     * @param int $channel
     * @throws \Exception
     */
    //$host, $port, $username, $password, $vhost, $channel
    public function __construct(array $host,array $port, string $username, string $password,string $vhost,int $channel)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->vhost = $vhost;
        $this->channel = $channel;
    }

    /**
     * Get instance
     * @return Rabbitmq
     */
    public function instance(): Rabbitmq
    {
        return $this->rabbitmq;
    }


    /**
     * Connect
     * @throws \Exception
     */
    public function connect()
    {
        try {
            $this->rabbitmq = new Rabbitmq(
                $this->host, $this->port, $this->username, $this->password, $this->vhost, $this->channel
            );
            $this->rabbitmq->connect();
        }catch (\Exception $exception){

        }


    }

    /**
     * Close
     */
    public function close()
    {
        if ( $this->rabbitmq !==null ){
            $this->rabbitmq->close();
        }

    }

}
