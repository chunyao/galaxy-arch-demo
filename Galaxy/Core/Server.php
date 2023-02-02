<?php

namespace Galaxy\Core;

use Galaxy\Common\Configur\Cache;
use Galaxy\Common\Configur\SnowFlake;
use Galaxy\Common\Configur\Upgrader;
use Galaxy\Common\XxlJob\XxlJobApi;
use Galaxy\Common\XxlJob\XxlJobVega;
use Logger;
use \Swoole;
use \Hyperf\Nacos\Application;
use \Hyperf\Nacos\Config;
use \GuzzleHttp;
use \Galaxy\Common\Handler\InnerServer;
use \Galaxy\Common\Configur\CoreDB;
use \Galaxy\Common\Configur\CoreRDS;

class Server
{
    protected Swoole\Http\Server $server;

    public static Swoole\Http\Server $serverinfo;

    protected array $config;

    public static array $localcache;

    public static array $innerConfig;

    public static string $trackId;

    public static array $bootConfig;

    protected array $coreConfig;

    public static \GuzzleHttp\Client $httpClient;

    protected array $headers;
    protected $vega;
    protected string $url;

    protected static string $appName;

    protected $tcpClient;

    private $wsVega;

    public function __construct($bootConfig)
    {
        self::$bootConfig = $bootConfig;
        Cache::init();
        echo "主进程ID:" . posix_getpid() . "\n";
        log::info("主进程ID:" . posix_getpid());
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
                log::info("注册中心进程ID:" . posix_getpid());
                swoole_timer_tick(25000, function () use ($bootConfig, $register) {
                    exec('rm -f ' . $bootConfig['log.path'] . "/" . $this->config['app.name'] . "/*" . date("Ymd", strtotime("-1 day")) . ".log");
                    self::$localcache = array();
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

        /* http server */
        $this->server = new Swoole\Http\Server("0.0.0.0", $serverPort);
        /* http server 健康检测 */
        $health = $this->server->addListener('0.0.0.0', $managementServerPort, SWOOLE_SOCK_TCP);
        $socket = $this->server->addlistener(ROOT_PATH . "/myserv.sock", 0, SWOOLE_UNIX_STREAM);

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
        $this->server->set(array(
            'reactor_num' => swoole_cpu_num(),
            'worker_num' => $this->config['worker.num'],
            'enable_coroutine' => true,
            'max_request' => $this->config['max.request'],
            'reload_async' => true,
            'dispatch_mode' => 3,
            'enable_deadlock_check' => false,
            'max_wait_time' => 6
        ));
        //集成 xxl job
        if (isset($this->config['xxl.job.enable']) && $this->config['xxl.job.enable']) {
            $xxlJobRegister = new XxlJobApi();
            $xxlJobRegister->XxlJobRegistry();
            $xxljob = $this->server->addListener('0.0.0.0', 9999, SWOOLE_SOCK_TCP);
            $xxljobVega = XxlJobVega::new();
            $xxljob->on('Request', $xxljobVega->handler());
            $process2 = new Swoole\Process(function ($worker) use ($xxlJobRegister) {
                swoole_timer_tick(25000, function () use ($xxlJobRegister) {
                    try {
                        $xxlJobRegister->XxlJobRegistry();
                    } catch (\Throwable $e) {
                        //var_dump($e);
                    }
                });
            }, false, 0, true);
            $process2->start();
        }
        $coreVega = CoreVega::new();

        $socket->on('Request', $coreVega->handler());
        $health->on('Request', $coreVega->handler());

        $this->server->on('open', function ($server, $request) {
        });
        $this->server->on('Start', function ($server) {

        });
        $this->server->on("ManagerStart", function ($server) {
            $rabbitMq = new RabbitMqProcess($this->config, 1, $this->url, $this->tcpClient);
            $rabbitMq->handler();
        });
        $this->server->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->server->on('WorkerStop', function ($server, $worker_id) {
            echo "工作进程停止: " . $worker_id;
        });
        $this->server->on('WorkerExit', array($this, 'onWorkerExit'));
        $this->server->on('WorkerError', array($this, 'onWorkerError'));

        $this->vega = Vega::new(self::$appName);
        $this->server->on('Request', array($this, 'onRequest'));

        $this->server->on('Receive', array($this, 'onReceive'));
        self::$serverinfo = $this->server;
    }

    public function httpStart()
    {
        $this->server->start();
    }

    public function onRequest($request, $response)
    {
        $this->vega->handler2($request, $response);
    }
    public function onMessage($request, $response)
    {
        $this->wsVega->handler2($request, $response);

    }

    public function onReceive($server, int $worker_id)
    {

        Log::info("进程:" . $worker_id . " exit");
    }

    public function onWorkerExit($server, int $worker_id)
    {

        Log::info("进程:" . $worker_id . " exit");
    }

    public function onWorkerError($server, int $worker_id)
    {
        Log::info("进程:" . $worker_id . " error");

        Log::info("服务器信息:" . $worker_id);
    }

    public function onWorkerStart($server, $worker_id)
    {
        echo "Worker 进程id:" . posix_getpid() . "\n";
        log::info("Worker 进程ID:" . posix_getpid());
        SnowFlake::init();
        Upgrader::init();
        //      CoreDB::enableCoroutine();
        //     CoreRDS::init($this->coreConfig);
        //     CoreRDS::enableCoroutine();
        /*自动加载用户配置*/


        $configs = ConfigLoad::findFile();

        foreach ($configs as $key => $val) {
            if ($val == "\\App\Config\\") continue;
            $val::init($this->config);
            $val::enableCoroutine();

        }
    }


}