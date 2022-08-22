<?php

namespace MabangSdk\Xxljob\Provider;

use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use MabangSdk\XxlJob\Commands\FlushTaskSceneCommand;
//use MabangSdk\XxlJob\Commands\StartXxlJobBeatCommand;

/**
 * Service provider class
 */
class MabangSdkXxlJobServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->registerCommands();
    }
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $app_name = env('APP_NAME');
            //心跳保活，只针对系统服务框架开启，统一管理，其他业务暂不开启服务
            if ($app_name == 'php_mabang_v2_system')
            {
                $this->commands([
                    FlushTaskSceneCommand::class,
                  //  StartXxlJobBeatCommand::class
                ]);
            }else{
                $this->commands([
                    FlushTaskSceneCommand::class
                ]);
            }
        }

    }
}