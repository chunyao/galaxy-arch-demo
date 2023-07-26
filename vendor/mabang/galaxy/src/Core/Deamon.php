<?php
namespace Mabang\Galaxy\Core;

use Galaxy\Core\Command;
use Galaxy\Core\Worker;
use Swoole\Process;

class Daemon
{
    /**
     * @var string
     */
    private $configPath;

    /**
     * @var Command[]
     */
    private $commands;

    /**
     * @var Worker[]
     */
    private $workers = [];

    public function __construct(string $configPath)
    {
        $this->configPath = $configPath;
    }

    public function run()
    {
        $this->loadWorkers();

        pcntl_signal(SIGHUP, function () {
            printf("收到重载配置信号\n");
            $this->loadWorkers();
            printf("重载配置完成\n");
        });

        $this->waitAndRestart();
    }

    /**
     * 收回进程并重启
     */
    private function waitAndRestart()
    {
        while (1) {
            pcntl_signal_dispatch();
            if ($ret = Process::wait(false)) {

                $retPid = intval($ret["pid"] ?? 0);
                $index = $this->getIndexOfWorkerByPid($retPid);

                if (false !== $index) {
                    if ($this->workers[$index]->isStopping()) {
                        printf("[%s] 移除守护 %s\n", date("Y-m-d H:i:s"), $this->workers[$index]->getCommand()->getId());

                        unset($this->workers[$index]);
                    } else {
                        $command = $this->workers[$index]->getCommand()->getCommand();
                        $newPid = $this->createWorker($command);
                        $this->workers[$index]->setPid($newPid);

                        printf("[%s] 重新拉起 %s\n", date("Y-m-d H:i:s"), $this->workers[$index]->getCommand()->getId());
                    }
                }

            }
        }
    }


    /**
     * 加载 workers
     */
    private function loadWorkers()
    {
        $this->parseConfig();
        foreach ($this->commands as $command) {
            if ($command->isEnabled()) {
                printf("[%s] 启用 %s\n", date("Y-m-d H:i:s"), $command->getId());
                $this->startWorker($command);
            } else {
                printf("[%s] 停用 %s\n", date("Y-m-d H:i:s"), $command->getId());
                $this->stopWorker($command);
            }
        }
    }

    /**
     * 启动 worker
     * @param Command $command
     */
    private function startWorker(Command $command)
    {
        $index = $this->getIndexOfWorker($command->getId());
        if (false === $index) {
            $pid = $this->createWorker($command->getCommand());

            $worker = new Worker();
            $worker->setPid($pid);
            $worker->setCommand($command);
            $this->workers[] = $worker;
        }
    }

    /**
     * 停止 worker
     * @param Command $command
     */
    private function stopWorker(Command $command)
    {
        $index = $this->getIndexOfWorker($command->getId());
        if (false !== $index) {
            $this->workers[$index]->setStopping(true);
        }
    }

    /**
     *
     * @param $commandId
     * @return bool|int|string
     */
    private function getIndexOfWorker(string $commandId)
    {
        foreach ($this->workers as $index => $worker) {
            if ($commandId == $worker->getCommand()->getId()) {
                return $index;
            }
        }
        return false;
    }

    /**
     * @param $pid
     * @return bool|int|string
     */
    private function getIndexOfWorkerByPid($pid)
    {
        foreach ($this->workers as $index => $worker) {
            if ($pid == $worker->getPid()) {
                return $index;
            }
        }
        return false;
    }

    /**
     * 解析配置文件
     */
    private function parseConfig()
    {
        if (is_readable($this->configPath)) {
            $iniConfig = parse_ini_file($this->configPath, true);

            $this->commands = [];
            foreach ($iniConfig as $id => $item) {
                $commandLine = strval($item["command"] ?? "");
                $enabled = boolval($item["enabled"] ?? false);

                $command = new Command();
                $command->setId($id);
                $command->setCommand($commandLine);
                $command->setEnabled($enabled);
                $this->commands[] = $command;
            }
        }
    }

    /**
     * 创建子进程，并返回子进程 id
     * @param $command
     * @return int
     */
    private function createWorker(string $command): int
    {
        $process = new Process(function (Process $worker) use ($command) {
            $worker->exec('/bin/sh', ['-c', $command]);
        });
        return $process->start();
    }

}