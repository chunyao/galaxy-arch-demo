<?php

namespace  Galaxy\Common\Mq\Pool;


use Galaxy\Common\Mq\Driver;
use Mix\ObjectPool\DialerInterface;

/**
 * Class Dialer
 * @package Mix\Database\Pool
 */
class Dialer implements DialerInterface
{

    /**
     * 数据源格式
     * @var string
     */
    protected $host = '';
    /**
     * 数据源格式
     * @var string
     */
    protected $port = '';

    /**
     * 数据库用户名
     * @var string
     */
    protected $username = 'root';

    /**
     * 数据库密码
     * @var string
     */
    protected $password = '';

    protected $vhost = '';

    protected $channel = '';


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
    public function __construct(array $hosts,array $port, string $username, string $password, $vhost,$channel)
    {
        $this->host = $hosts;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->vhost = $vhost;
        $this->channel  = $channel;

    }

    /**
     * Dial
     * @return Driver
     * @throws \Exception
     */
    public function dial(): object
    {

        return new Driver(
            $this->host, $this->port, $this->username, $this->password, $this->vhost, $this->channel
        );
    }

}
