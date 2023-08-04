<?php

namespace  Mabang\Galaxy\Common\HttpClient\Pool;

use Mabang\Galaxy\Common\HttpClient\Driver;
use Mix\ObjectPool\AbstractObjectPool;

/**
 * Class ConnectionPool
 * @package Mix\Redis\Pool
 */
class ConnectionPool extends AbstractObjectPool
{

    /**
     * 借用连接
     * @return Driver
     */
    public function borrow(): object
    {
        return parent::borrow();
    }

}
