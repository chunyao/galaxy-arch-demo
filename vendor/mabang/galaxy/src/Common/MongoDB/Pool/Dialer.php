<?php

namespace  Mabang\Galaxy\Common\MongoDB\Pool;


use Mabang\Galaxy\Common\MongoDB\Driver;
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
    protected $config = '';



    /**
     * Driver constructor.
     * @param array $config

     * @throws \Exception
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Dial
     * @return Driver
     * @throws \Exception
     */
    public function dial(): object
    {
        return new Driver(
            $this->config
        );
    }

}
