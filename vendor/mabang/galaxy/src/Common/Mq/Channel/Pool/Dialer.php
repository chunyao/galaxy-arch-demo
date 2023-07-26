<?php

namespace  Mabang\Galaxy\Common\Mq\Channel\Pool;

use Mabang\Galaxy\Common\Mq\Channel\Driver;
use Mix\ObjectPool\DialerInterface;

/**
 * Class Dialer
 * @package Mix\Database\Pool
 */
class Dialer implements DialerInterface
{

    protected $connect ;


    public function __construct($connect)
    {
        $this->connect  = $connect;
    }

    /**
     * Dial
     * @return Driver
     * @throws \Exception
     */
    public function dial(): object
    {

        return new Driver(
            $this->connect
        );
    }

}
