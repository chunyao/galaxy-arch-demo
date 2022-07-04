<?php

namespace Galaxy\Core;

use App\Listener\TestListener;
use App;

class RobbitMqListener
{
    protected $msg;
    protected $queue;
    protected static $config;
    public static $mqClasses;


    public function __construct($msg, $queue, &$config)
    {
        $this->msg = $msg;
        $this->queue = $queue;
        self::$config = $config;
        $this->rabbitQueueload(self::$config['app.name']);
    }

    public static function rabbitQueueload($appName)
    {

        $dir = ROOT_PATH . DIRECTORY_SEPARATOR .$appName .
            DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."App".
            DIRECTORY_SEPARATOR."Listener".DIRECTORY_SEPARATOR;

        if (is_dir($dir)) {

            $info = opendir($dir);
            $i = 0;

            while (($file = readdir($info)) !== false) {

                if (is_file($dir.$file)) {

                    $class = str_replace(".php", "", $file);

                    self::$mqClasses[$i] = 'App\Listener\\'.$class;
                }

            }
            closedir($info);

        }
        return self::$mqClasses;
    }

    public function handler()
    {

        foreach (self::$mqClasses as $key => $val) {

            if ($val::$queueName==$this->queue){

                $consumer = new $val($this->msg);

                $result = $consumer->handler();

                unset($consumer);
                return $result;
            };
        }

    }

    public function getMsg()
    {
        return $this->msg;
    }

    public function getQueue()
    {
        return $this->queue;
    }


}