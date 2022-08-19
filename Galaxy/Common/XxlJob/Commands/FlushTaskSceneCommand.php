<?php

namespace MabangSdk\XxlJob\Commands;

use Illuminate\Console\Command;
use MabangSdk\XxlJob\Facades\XxlJob;
use MabangSdk\XxlJob\Support\XxlJob\XxlJobService;


class FlushTaskSceneCommand extends Command
{
    /**
     * @var string
     */
    protected $name = 'XxlJob:FlushScene';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '刷新任务映射场景配置缓存';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response =  XxlJob::FlushTaskScene();

        if ($response) {
            $this->info('XxlJobRegistry:All scene set cache successfully!');
        }
    }
}
