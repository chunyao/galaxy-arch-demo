<?php

namespace Galaxy\Core;

use Swoole;
class PoolServer
{
    protected $pool;

    protected $workerNum;

    public function __construct($bootConfig)
    {
        Log::init();
        $this->workerNum=10;
        /* 进程池 server */
        $this->pool = new Swoole\Process\Pool($this->workerNum);
        $this->pool->set(['enable_coroutine' => true]);

        $this->pool->on("WorkerStart", function ($pool, $workerId) {
            echo "Worker#{$workerId} is started \n";
        });
        $this->pool->on("WorkerStop", function ($pool, $workerId) {
            echo "Worker#{$workerId} is stopped \n";
        });
    }
    public function poolStart(){
        $this->pool->start();
    }

}