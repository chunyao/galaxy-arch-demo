<?php

namespace Galaxy\Common\Handler;

use App;
use Galaxy\Core\RobbitMqListener;
use App\Listener;

class InnerServer
{
    protected $route;


    protected $action;

    protected $data;

    protected static $config;

    public function __construct($action, $data, &$config)
    {
        $this->action = str_replace("http://127.0.0.1:".App::$bootConfig['management.server.port']."/", "", $action);
        $this->data = $data;

        $this->route = [
            "rabbitmq",
        ];
        self::$config = $config;
        // $this->rabbitmq();
    }

    public function rabbitmq()
    {
        $mq = new RobbitMqListener($this->data['message'], $this->data['queue'], self::$config);

        $result = $mq->handler();
        unset($mq);
        return $result;
    }


    public function handler()
    {   $result = null;
        foreach ($this->route as $key => $val) {

            if ($val == $this->action) {
                $result = call_user_func(array($this, $val));

            }
        }
        return $result;
    }
}