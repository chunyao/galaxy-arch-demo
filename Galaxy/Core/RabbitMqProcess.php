<?php

namespace Galaxy\Core;

use Galaxy\Common\Configur\SnowFlake;
use Swoole;

class RabbitMqProcess
{
    private $channel_start = 20;
    private $works = [];
    private $workers = 1;
    private $processes = [];

    private $config;

    protected $url;

    protected $tcpClient;

    public function __construct($config, $workers, $url, $tcpClient)
    {

        $this->config = $config;
        $this->workers = $workers;
        $this->url = $url;


    }

    public function createProcess($ch, $queue)
    {

        $process = new Swoole\Process(function ($worker) use ($ch, $queue) {
            //    while (1) {
            //      sleep(5);
            Log::info("消息进程ID:" . posix_getpid() . "\n");
            try {
                $consumer = new ConsumerRabbit($this->config, $this->url);
                $consumer->initQueues($ch, $queue);
            } catch (\Throwable $e) {
                Log::error(sprintf('%s in %s on line %d', $e->getMessage(), $e->getFile(), $e->getLine()));
            }

            //       }
        }, false, 0, true);

        $pid = $process->start();
        $this->works[$pid] = $queue;
        $this->processes[$pid] = $process;
        echo "Mq Master: new worker, PID=" . $pid . "\n";
        return $pid;
    }

//    public function watchProcess()
//    {
//        while (1) {
//            if ($ret = Swoole\Process::wait(false)) {
//                $retPid = intval($ret["pid"] ?? 0);
//                if (isset($this->works[$retPid])) {
//                    $this->createProcess(rand(0,100),$this->works[$retPid]);
//                    unset($this->works[$retPid]);
//                    unset($this->processes[$retPid]);
//                }
//            }
//        }
//    }

    public function handler()
    {
        if (empty($this->config['rabbitmq.enable'])) return;
        $channel_step = 0;
        for ($worker = 0; $worker < $this->workers; $worker++) {

            $i = 0;
            foreach ($this->config['rabbitmq.enable'] as $key => $value) {

                if ($value) {
                    foreach (RobbitMqListener::rabbitQueueload($this->config['app.name']) as $key => $val) {
                        if ($val::getQueue() == $this->config['rabbitmq.queue'][$i]) $this->createProcess($this->channel_start + $channel_step, $i);
                    }
                }
                $i++;
                $channel_step++;
            }
        }
        // $this->watchProcess();
    }
}
