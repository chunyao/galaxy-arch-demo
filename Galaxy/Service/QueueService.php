<?php

namespace Galaxy\Service;

use \Galaxy\Repository\Cache\RabbitmqScene;

class QueueService
{
    private RabbitmqScene $rabbitmqScene;

    public function __construct()
    {
        $this->rabbitmqScene = new RabbitmqScene();

    }

    public function findByQueueName($quename)
    {

        $data = $this->rabbitmqScene->findByQueueName($quename);

        return $data;
    }

    public function __destruct()
    {
        unset($this->rabbitmqSceneModel);
    }
}