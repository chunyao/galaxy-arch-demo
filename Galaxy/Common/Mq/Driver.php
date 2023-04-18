<?php

namespace Galaxy\Common\Mq;

use Galaxy\Common\Mq\Channel\Channel;
use Galaxy\Core\Log;
use Galaxy\Core\Once;
use Mix\ObjectPool\ObjectTrait;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnectionConfig;
use PhpAmqpLib\Connection\AMQPConnectionFactory;

/**
 * Class Driver
 * @package Mix\Database
 */
class Driver
{

    use ObjectTrait;

    protected $config;
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
     * @var Channel
     */
    protected static $channel;

    public $con;

    /**
     * @var array
     */
    protected $options = [];


    private static $once;

    /**
     * Driver constructor.
     * @param array $hosts
     * @param array $port
     * @param string $username
     * @param string $password
     * @param string $vhost

     * @throws \Exception
     */
    //$host, $port, $username, $password, $vhost, $channel
    public function __construct(array $host, array $port, string $username, string $password, string $vhost, int $channel)
    {
        static::$once = new Once();

        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->vhost = $vhost;

        $config = new AMQPConnectionConfig();
        if (count($this->host) > 1) {
            $cur = rand(0, 2);
            $config->setHost($this->host[$cur]);
            $config->setPort($this->port[$cur]);
        } elseif (count($this->host) == 1) {
            $config->setHost($this->host);
            $config->setPort($this->port);
        }
        $config->setUser($this->username);
        $config->setPassword($this->password);
        $config->setVhost($this->vhost);
        $config->setInsist(false);
        $config->setLoginMethod('AMQPLAIN');
        $config->setConnectionTimeout(10);
        $config->setLocale('en_US');
        $config->setLoginResponse("");
        $config->setReadTimeout(1800);
        $config->setKeepalive(false);
        $config->setWriteTimeout(1800);
        $config->setHeartbeat(900);
        $config->setIoType(AMQPConnectionConfig::IO_TYPE_STREAM);
        $this->config = $config;
        $this->con = AMQPConnectionFactory::create($this->config);
        $this->connect();
    }

    public function reconnect()
    {
        $this->con = AMQPConnectionFactory::create($this->config);
        $this->connect();
    }


    public function instance()
    {
        return $this->rabbitmq;
    }

//    public static function instChannel(): Channel
//    {
//        if (!isset(self::$channel)) {
//            static::$once->do(
//            /**
//             * @throws \Exception
//             */
//                function () {
//                    self::$channel = new Channel();
//                });
//
//        }
//
//        return self::$channel;
//    }

    /**
     * Connect
     * @throws \Exception
     */
    public function connect()
    {

        try {
            $this->rabbitmq = $this->con;
        } catch (\Exception $exception) {
            $this->reconnect();
            $this->rabbitmq = $this->con;
            Log::error(['reconnect' => $exception->getMessage()]);
        }
    }

    /**
     * Close
     */
    public function close()
    {
        if ($this->rabbitmq !== null) {
            $this->rabbitmq->close();
        }

    }

    public function closeCon()
    {
        if ($this->rabbitmq !== null) {
            $this->rabbitmq->close();
            $this->con->close();
        }

    }

}
