<?php

namespace  Galaxy\Common\MongoDB\Pool;

use Mix\ObjectPool\AbstractObjectPool;

class ConnectionPool extends AbstractObjectPool
{


    public function borrow(): object
    {
        return parent::borrow();
    }

}
