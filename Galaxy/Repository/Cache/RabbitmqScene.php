<?php

namespace Galaxy\Repository\Cache;

use Galaxy\Repository\Model\RabbitmqSceneModel;
use Galaxy\Common\Configur\CoreRDS;

class RabbitmqScene
{
    private RabbitmqSceneModel $rabbitmqSceneModel;
    private const FindByQueueName = 'findByQueueName';

    public function __construct()
    {
        $this->rabbitmqSceneModel = new RabbitmqSceneModel();
        $this->table = 'rabbitmq_scene';
    }

    public function findByQueueName($quename):array
    {
        if (($data = json_decode(CoreRDS::instance()->get("FindByQueueName"),1)) !== null) {
            return $data;
        } else {
            CoreRDS::instance()->set("FindByQueueName", json_encode($this->rabbitmqSceneModel->findByQueueName($quename)),300);
            return CoreRDS::instance()->get("FindByQueueName");
        }
    }
}