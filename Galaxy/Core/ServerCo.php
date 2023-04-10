<?php

namespace Galaxy\Core;

use Galaxy\Common\Configur\Cache;
use Galaxy\Common\Configur\SnowFlake;
use Galaxy\Common\Configur\Upgrader;
use Galaxy\Common\XxlJob\XxlJobApi;
use Galaxy\Common\XxlJob\XxlJobVega;
use \Swoole;
use \Hyperf\Nacos\Application;
use \Hyperf\Nacos\Config;
use \GuzzleHttp;


class ServerCo
{

    protected array $config;

    protected static array $bootConfig;

    public static array $innerConfig;

    public static \GuzzleHttp\Client $httpClient;

    protected array $headers;

    protected $vega;

    protected string $url;

    protected static string $appName;

    protected $tcpClient;


    public function start($bootConfig)
    {
        self::$bootConfig = $bootConfig;
        Cache::init();
        echo "主进程ID:" . posix_getpid() . "\n";
        Log::info("主进程ID:" . posix_getpid());
        self::$httpClient = new GuzzleHttp\Client();

        $this->url = 'http://127.0.0.1:' . $bootConfig['management.server.port'] . '/rabbitmq';
        $this->headers = ["Content-Type" => 'application/json'];

        $application = new Application(new Config([
            'base_uri' => $bootConfig['url'],
            'guzzle_config' => [
                'headers' => [
                    'charset' => 'UTF-8',
                ],
            ],
        ]));

        if ($bootConfig['env'] == "local") {
            $this->config = parse_ini_file(ROOT_PATH . '/local.ini');
            self::$innerConfig = $this->config;
        } else {
            $application = new Application(new Config([
                'base_uri' => $bootConfig['url'],
                'guzzle_config' => [
                    'headers' => [
                        'charset' => 'UTF-8',
                    ],
                ],
            ]));
            //          $application->auth->login($bootConfig['user'], $bootConfig['password']);

            $response = $application->config->get($bootConfig['dataId'], $bootConfig['group'], $bootConfig['tenant']);
            $this->config = parse_ini_string((string)$response->getBody());
            self::$innerConfig = $this->config;

            $register = new ServiceRegister($bootConfig['url'], $this->config['app.name'], $this->config['namespace.id']);
            $register->handle("register");

            $process = new Swoole\Process(function () use ($bootConfig, $register) {
                echo "注册中心进程ID:" . posix_getpid() . "\n";
                Log::info("注册中心进程ID:" . posix_getpid());
                swoole_timer_tick(25000, function () use ($bootConfig, $register) {
                    Cache::instance()->removeTimeOut();
                    exec('rm -f ' . $bootConfig['log.path'] . "/" . $this->config['app.name'] . "/*" . date("Ymd", strtotime("-1 day")) . ".log");
                    try {
                        $register->beat();
                    } catch (\Throwable $e) {
                    }
                });
            }, false, 0, true);
            $process->start();
        }


        self::$appName = $this->config['app.name'];


        if (isset($bootConfig['server.port'])) {
            $serverPort = $bootConfig['server.port'];
        } elseif (isset($this->config['server.port'])) {
            $serverPort = $this->config['server.port'];
        } else {
            $serverPort = 8080;
        }

        if (isset($bootConfig['management.server.port'])) {
            $managementServerPort = $bootConfig['management.server.port'];
        } elseif (isset($this->config['management.server.port'])) {
            $managementServerPort = $this->config['management.server.port'];
        } else {
            $managementServerPort = 8081;
        }


        echo <<<EOL
  __  __      ___                        _         
 |  \/  |__ _| _ ) __ _ _ _  __ _   _ __| |_  _ __ 
 | |\/| / _` | _ \/ _` | ' \/ _` | | '_ \ ' \| '_ \
 |_|  |_\__,_|___/\__,_|_||_\__, | | .__/_||_| .__/
                            |___/  |_|       |_|   

EOL;
        printf("System    Name:       %s\n", strtolower(PHP_OS));
        printf("PHP       Version:    %s\n", PHP_VERSION);
        printf("Swoole    Version:    %s\n", swoole_version());
        printf("Http   Listen    Addr:       http://%s:%d\n", "0.0.0.0", $serverPort);
        printf("Health Listen    Addr:       http://%s:%d\n", "0.0.0.0", $managementServerPort);
        Log::info('Start http server');
        $scheduler = new \Swoole\Coroutine\Scheduler;
        $scheduler->set([
            'hook_flags' => SWOOLE_HOOK_ALL ,SWOOLE_HOOK_NATIVE_CURL
        ]);

        $scheduler->parallel(1,function () use ($serverPort) {
            echo "Http异步服务" . PHP_EOL;
            SnowFlake::init();
            $configs = ConfigLoad::findFile();
            foreach ($configs as $key => $val) {
                if ($val == "\\App\Config\\") continue;
                $val::init($this->config);
                $val::enableCoroutine();

            }
            $vega = Vega::new(self::$appName);
            $server = new Swoole\Coroutine\Http\Server("0.0.0.0", $serverPort, false, false);
            $server->handle('/', $vega->handler());
//            foreach ([SIGHUP, SIGINT, SIGTERM] as $signal) {
//                Swoole\Process::signal($signal, function () use ($server) {
//                    Log::info('Shutdown swoole coroutine server');
//                    $server->shutdown();
//                });
//            }
            $server->start();
        });

        $scheduler->add(function () use ($managementServerPort) {
            echo "管理服务" . PHP_EOL;
            SnowFlake::init();
            $configs = ConfigLoad::findFile();
            foreach ($configs as $key => $val) {
                if ($val == "\\App\Config\\") continue;
                $val::init($this->config);
                $val::enableCoroutine();

            }
            $coreVega = CoreVega::new();
            $health = new Swoole\Coroutine\Http\Server("0.0.0.0", $managementServerPort, false, false);
            $health->handle('/', $coreVega->handler());
//            foreach ([SIGHUP, SIGINT, SIGTERM] as $signal) {
//                Swoole\Process::signal($signal, function () use ($health) {
//                    Log::info('Shutdown swoole coroutine server');
//                    $health->shutdown();
//                });
//            }
            $rabbitMq = new RabbitMqProcess($this->config, 1, $this->url, $this->tcpClient);
            $rabbitMq->handler();
            $health->start();
        });

        if (isset($this->config['xxl.job.enable']) && $this->config['xxl.job.enable']) {
            $scheduler->add(function () {
                echo "xxl-job服务" . PHP_EOL;
                SnowFlake::init();
                $configs = ConfigLoad::findFile();

                foreach ($configs as $key => $val) {
                    if ($val == "\\App\Config\\") continue;
                    $val::init($this->config);
                    $val::enableCoroutine();

                }
                $xxlJobRegister = new XxlJobApi();
                $xxlJobRegister->XxlJobRegistry();
                $xxljob = new Swoole\Coroutine\Http\Server("0.0.0.0", 9999, false, false);
                $xxljobVega = XxlJobVega::new();
                $xxljob->handle('/', $xxljobVega->handler());
                swoole_timer_tick(25000, function () use ($xxlJobRegister) {
                    try {
                        $xxlJobRegister->XxlJobRegistry();
                    } catch (\Throwable $e) {
                        //var_dump($e);
                    }
                });

                $xxljob->start();

            });
        }
        $scheduler->start();
    }


}