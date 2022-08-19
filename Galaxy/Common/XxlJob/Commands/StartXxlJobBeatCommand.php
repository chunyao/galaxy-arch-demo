<?php

namespace MabangSdk\XxlJob\Commands;

use MabangSdk\XxlJob\Facades\XxlJob;
use Illuminate\Console\Command;

class StartXxlJobBeatCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '----XxlJob:BeatStart';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'start beat xxl job  暂时不使用';

    /**
     * @return string
     */
    public function display(): string
    {
        return '心跳检测服务触发 暂不使用';
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response =  XxlJob::XxlJobRegistry();

        if ($response) {
            $this->info('XxlJobRegistry:All config send XxlJob Registry successfully!');
        }
    }
}
