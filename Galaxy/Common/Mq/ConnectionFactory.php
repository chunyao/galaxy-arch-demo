<?php

namespace Galaxy\Common\Mq;


use Hyperf\Amqp\IO\SwooleIO;
use Hyperf\Amqp\Params;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Coroutine\Locker;
use Psr\Container\ContainerInterface;

class ConnectionFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var AMQPConnection[][]
     */
    protected $connections = [];

    public function __construct($config)
    {

        $this->config = $config;
    }

    public function refresh(string $pool)
    {

        $count = 100;

        if (Locker::lock(static::class)) {
            try {
                for ($i = 0; $i < $count; ++$i) {
                    $connection = $this->make( $this->config);
                    $this->connections[$pool][] = $connection;
                }
            } finally {
                Locker::unlock(static::class);
            }
        }
    }

    public function getConnection(string $pool): AMQPConnection
    {
        if (! empty($this->connections[$pool])) {
            $index = array_rand($this->connections[$pool]);
            $connection = $this->connections[$pool][$index];
            if (! $connection->isConnected()) {
                if (Locker::lock(static::class . 'getConnection')) {
                    try {
                        unset($this->connections[$pool][$index]);
                        $connection->close();
                        $connection = $this->make($this->config);
                        $this->connections[$pool][] = $connection;
                    } finally {
                        Locker::unlock(static::class . 'getConnection');
                    }
                } else {
                    return $this->getConnection($pool);
                }
            }

            return $connection;
        }

        $this->refresh($pool);
        return Arr::random($this->connections[$pool]);
    }

    public function make(array $config): AMQPConnection
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 5672;
        $user = $config['user'] ?? 'guest';
        $password = $config['password'] ?? 'guest';
        $vhost = $config['vhost'] ?? '/';
        $openSSL = $config['open_ssl'] ?? false;

        $params = new Params(Arr::get($config, 'params', []));
        $io = new SwooleIO(
            $host,
            $port,
            $params->getConnectionTimeout(),
            $params->getReadWriteTimeout(),
            $openSSL,
        );

        $connection = new AMQPConnection(
            $user,
            $password,
            $vhost,
            $params->isInsist(),
            $params->getLoginMethod(),
            $params->getLoginResponse(),
            $params->getLocale(),
            $io,
            $params->getHeartbeat(),
            $params->getConnectionTimeout(),
            $params->getChannelRpcTimeout()
        );

        return $connection->setParams($params);
    }

}