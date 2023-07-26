<?php

namespace Mabang\Galaxy\Core;

use Mabang\Galaxy\Common\Configur\Cache;
use Mabang\Galaxy\Common\Configur\TraceRecord;
use Mabang\Galaxy\Common\Configur\Loggers;
use Mabang\Galaxy\Common\Configur\SnowFlake;
use Mabang\Galaxy\Common\XxlJob\XxlJobApi;
use Mabang\Galaxy\Common\XxlJob\XxlJobVega;
use Logger;
use Mabang\Galaxy\Core\ConfigLoad;
use Mabang\Galaxy\Core\CoreVega;
use Mabang\Galaxy\Core\Error;
use Mabang\Galaxy\Core\Log;
use Mabang\Galaxy\Core\RabbitMqProcess;
use Mabang\Galaxy\Core\ServiceRegister;
use Mabang\Galaxy\Core\Vega;

use \Swoole;
use \Hyperf\Nacos\Application;
use \Hyperf\Nacos\Config;
use \GuzzleHttp;
use Mabang\Galaxy\Common\Handler\InnerServer;
use Mabang\Galaxy\Common\Configur\CoreDB;
use Mabang\Galaxy\Common\Configur\CoreRDS;
use Swoole\Process;

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

    private $mongoDrvier;

    public function __construct($bootConfig)
    {
        self::$bootConfig = $bootConfig;
        Cache::init();
        Log::init();
        Error::register();
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
            $this->mongoDrvier = ROOT_PATH . "/app";
        } else {
            $this->mongoDrvier = "/data/app";
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
        echo "CPU: ".swoole_cpu_num().PHP_EOL;
        $this->server->set(array(
            'reactor_num' => swoole_cpu_num(),
            'worker_num' => $this->config['worker.num'],
            'enable_coroutine' => true,
            'max_request' => $this->config['max.request'],
            'reload_async' => true,
            //   'dispatch_mode' => 3,
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

            //  $addr = '127.0.0.1:6001';
            if ($this->config['mongo.host'] !== null && (count($this->config['mongo.host']) > 1 || is_array($this->config['mongo.host']))) {
                for ($d = 0; $d < (count($this->config['mongo.host'])); $d++) {
                    $process = new Process(function (Process $process) use ($d) {

                        //  $addr = ROOT_PATH . '/' . md5($this->config['mongo.host'][$d] . $this->config['mongo.user'] [$d] . $this->config['mongo.database'][$d]) . '.sock';
                        $addr = '127.0.0.1:' . $this->config['mongo.pool.port'][$d];
                        echo 'MongoDB: ' . $addr . PHP_EOL;
                        $process->exec($this->mongoDrvier, [
                            '-address', $addr,
                            '-mongodb-uri', 'mongodb://' . $this->config['mongo.host'][$d],
                            '-mongodb-username', $this->config['mongo.user'][$d],
                            '-mongodb-password', $this->config['mongo.password'][$d],
                            '-mongodb-database', $this->config['mongo.database'][$d],
                            '-mongodb-replicaset', $this->config['mongo.replicaset'][$d],
                            '-mongodb-poolMax', $this->config['mongo.maxOpen'][$d] ?? 50,
                            '-mongodb-poolMin', $this->config['mongo.maxIdle'][$d] ?? 50,
                            '-mongodb-IdleTime', ($this->config['mongo.maxLifetime'][$d] ?? 3600) . 's',
                            '-mongodb-connect-timeout', '5s',
                            '-mongodb-read-write-timeout', '60s'
                        ],
                        );

                    });
                    $process->start();
                }
            } elseif (isset($this->config['mongo.host']) && count($this->config['mongo.host']) == 1) {

                $process = new Process(function (Process $process) {
                    //  $addr = ROOT_PATH . '/' . md5($this->config['mongo.host'] . $this->config['mongo.user'] . $this->config['mongo.database']) . '.sock';
                    $addr = '127.0.0.1:' . $this->config['mongo.pool.port'];
                    $process->exec($this->mongoDrvier, [
                        '-address', $addr,
                        '-mongodb-uri', 'mongodb://' . $this->config['mongo.host'],
                        '-mongodb-username', $this->config['mongo.user'],
                        '-mongodb-password', $this->config['mongo.password'],
                        '-mongodb-database', $this->config['mongo.database'],
                        '-mongodb-replicaset', $this->config['mongo.replicaset'],
                        '-mongodb-poolMax', $this->config['mongo.maxOpen'] ?? 50,
                        '-mongodb-poolMin', $this->config['mongo.maxIdle'] ?? 5,
                        '-mongodb-IdleTime', $this->config['mongo.maxLifetime'] ?? 3600,
                        '-mongodb-connect-timeout', '5s',
                        '-mongodb-read-write-timeout', '60s'
                    ],
                    );
                });
                $process->start();
            }
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

        Log::info("Worker 进程ID:" . posix_getpid());
        SnowFlake::init();
        TraceRecord::init();
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