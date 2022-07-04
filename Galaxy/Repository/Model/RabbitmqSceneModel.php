<?php

namespace Galaxy\Repository\Model;

use Galaxy\Common\Configur\CoreDb;

class RabbitmqSceneModel
{

    private $table;
    public function __construct()
    {
        $this->table = 'rabbitmq_scene';
    }

    public function findByQueueName($quename)
    {
        $data = CoreDb::instance()->table( $this->table )->where('queue_name = ?', $quename)->first();
        return $data;

    }


    public function __destruct()
    {

    }
}