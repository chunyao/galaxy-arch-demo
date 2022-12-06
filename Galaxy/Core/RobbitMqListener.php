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
        self::rabbitQueueload(self::$config['app.name']);
    }
    static  function getDir($path)
    {
        //判断目录是否为空
        if(!file_exists($path)) {
            return [];
        }

        $files = scandir($path);
        $fileItem = [];
        foreach($files as $v) {
            $newPath = $path .DIRECTORY_SEPARATOR . $v;
            if(is_dir($newPath) && $v != '.' && $v != '..') {
                $fileItem = array_merge($fileItem, self::getDir($newPath));
            }else if(is_file($newPath)){
                $fileItem[] = $newPath;
            }
        }

        return $fileItem;
    }

    public static function rabbitQueueload($appName)
    {

        $dir = ROOT_PATH  .
            DIRECTORY_SEPARATOR."service-main".DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."App".
            DIRECTORY_SEPARATOR."Listener".DIRECTORY_SEPARATOR;
        $i = 0;
        foreach (self::getDir($dir) as $item){

            $class = str_replace(".php", "", str_replace("/", "\\", explode("//",$item)[1]));

            self::$mqClasses[$i] = 'App\Listener\\' . $class;
            $i++;
        }
        return self::$mqClasses;
    }

    public function handler()
    {

        foreach (self::$mqClasses as $key => $val) {

            if ($val::getQueue()==$this->queue){

                $consumer = new $val($this->msg);

                $result = $consumer->handler();

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