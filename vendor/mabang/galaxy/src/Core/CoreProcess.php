<?php
namespace Mabang\Galaxy\Core;

use Galaxy\Core\swoole_process;

class CoreProcess
{
    public $mpid = 0;
    public $works = [];
    public $workers = 1;
    public $processes = [];
    public $new_index = 1;
    public $create_time = 0;

    public function __construct()
    {
        swoole_async_set(['enable_coroutine' => false]); // Process中仅用协程

        // 由于所有进程是共享使用一个消息队列，所以只需向一个子进程发送消息即可 - 注意队列大小限制
        try {
            if (!preg_match('/Darwin/', php_uname())) {
                swoole_set_process_name(sprintf('php-ps:%s', 'master'));
            }
            $this->mpid = posix_getpid();
            $this->run();

            $process = current($this->processes);

            swoole_timer_tick(1000, function () use ($process) {
                 $data = "kick";
                // push data

            //    $process->push($data);
            });
            $this->processWait();
        } catch (\Exception $e) {
            var_dump($e);
        }
    }

    public function run()
    {
        for ($i = 0; $i < $this->workers; $i++) {
           echo  "create".$this->createProcess($i)."\n";
        }
    }
    public function checkHealth(){
        foreach( $this->processes as $pid => $process)
        {
            $process->push("hello worker[$pid]\n");
            $result = $process->pop();
            echo "From worker: $result\n";//这里主进程，接受到的子进程的数据
        }

    }

    public function createProcess($index = null)
    {
        $process = new swoole_process(function (swoole_process $worker) use ($index) {
            if (is_null($index)) {
                $index = $this->new_index;
                $this->new_index++;
            }

            if (!preg_match('/Darwin/', php_uname())) {
                try {
                    swoole_set_process_name(sprintf('php-ps:%s', $index));
                } catch (\Exception $e) {
                    var_dump('ALL ERROR:' . $e->getMessage());
                }
            }
            $this->StartMq($index);


        }, false, false);


        $pid = $process->start();
        $this->works[$index] = $pid;
        $this->processes[$pid] = $process;
        echo "Master: new worker, PID=".$pid."\n";
        return $pid;
    }



    public function rebootProcess($ret)
    {
        $pid = $ret['pid'];
        $index = array_search($pid, $this->works);

        if (false !== $index) {
            $index = intval($index);
            $new_pid = $this->createProcess($index);
            return $new_pid;
        }
    }

    public function processWait()
    {
        swoole_timer_tick(1000, function () {
            if (count($this->works)) {
                $ret = swoole_process::wait();
                if ($ret) {
                    $this->rebootProcess($ret);
                }
            }
        });
    }

    private function StartMq($data)
    {

    }
}