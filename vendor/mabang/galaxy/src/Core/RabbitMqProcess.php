<?php

namespace Mabang\Galaxy\Core;

use App\Config\HttpClient;
use Mabang\Galaxy\Core\ConsumerRabbit;
use Mabang\Galaxy\Core\Log;
use Mabang\Galaxy\Core\RobbitMqListener;
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
    
            $child = null;
            Log::info("消息进程ID:" . posix_getpid() . "\n");
            try {
                $consumer = new ConsumerRabbit($this->config, $this->url);
                $child = $consumer->initQueues($ch, $queue);
            } catch (\Throwable $e) {
                Log::error(sprintf('%s in %s on line %d', $e->getMessage(), $e->getFile(), $e->getLine()));
            }
            while (1) {
                sleep(5);
              //  echo $child->getRunningCoroutineCount().PHP_EOL;
                if ($child != null && $child->getRunningCoroutineCount()==0) {
                    Log::info("消息进程ID:" . posix_getpid() . "\n");
                    try {
                        $consumer = new ConsumerRabbit($this->config, $this->url);
                        $child = $consumer->initQueues($ch, $queue);
                    } catch (\Throwable $e) {
                        Log::error(sprintf('%s in %s on line %d', $e->getMessage(), $e->getFile(), $e->getLine()));
                    }
                }

            }
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
                        if (isset($this->config['rabbitmq.queue.num'][$i])) {
                            for ($k = 0; $k < 1; $k++) {
                                if ($val::getQueue() == $this->config['rabbitmq.queue'][$i]) $this->createProcess($this->channel_start + $channel_step, $i);
                            }
                        } else {
                            if ($val::getQueue() == $this->config['rabbitmq.queue'][$i]) $this->createProcess($this->channel_start + $channel_step, $i);
                        }

                    }
                }
                $i++;
                $channel_step++;
            }
        }
        // $this->watchProcess();
    }
}
