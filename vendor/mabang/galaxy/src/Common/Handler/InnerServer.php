<?php

namespace Mabang\Galaxy\Common\Handler;

use App;
use Galaxy\Common\Handler\RedisListener;
use Mabang\Galaxy\Core\Container;
use Mabang\Galaxy\Core\RobbitMqListener;
use App\Listener;
use Mabang\Galaxy\Common\Annotation\Autowired;

class InnerServer
{
    protected $route;

    protected $action;

    protected $data;

    protected static $config;
    /**
     * @Autowired()
     * @var RobbitMqListener
     */
    private RobbitMqListener $robbitMqListener;

    public function rabbitmq()
    {

        return $this->robbitMqListener->handler($this->data['message'], $this->data['queue'], self::$config);
    }

    public function redis()
    {
        $mq = new RedisListener($this->data['message'], $this->data['queue'], self::$config);

        $result = $mq->handler();
        unset($mq);

        return true;
    }


    public function handler($action, $data, &$config)
    {
        $this->action = str_replace("http://127.0.0.1:" . App::$bootConfig['management.server.port'] . "/", "", $action);
        $this->data = $data;

        $this->route = [
            "rabbitmq",
            "redis",
        ];
        self::$config = $config;
        $result = null;
        foreach ($this->route as $key => $val) {

            if ($val == $this->action) {
                $result = call_user_func(array($this, $val));

            }
        }
        return $result;
    }
}