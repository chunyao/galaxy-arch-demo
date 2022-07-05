<?php

namespace Galaxy\Core;


use Swoole;
use Hyperf\Nacos\Application;
use Hyperf\Nacos\Config;
use \GuzzleHttp;
use Galaxy\Common\Handler\InnerServer;
use Galaxy\Common\Configur\CoreDB;
use Galaxy\Common\Configur\CoreRDS;

class Server
{
    protected Swoole\Http\Server $server;

    protected array $config;

    protected array $coreConfig;

    public static InnerServer $innerServer;

    public static \GuzzleHttp\Client $httpClient;

    protected array $headers;

    protected string $url;

    protected string $appName;

    protected $tcpClient;

    public function __construct($bootConfig)
    {
        Log::init();

        self::$httpClient = new GuzzleHttp\Client();
        $this->url = 'http://127.0.0.1:8081/rabbitmq';
        $this->headers = ["Content-Type" => 'application/json'];
        if ($bootConfig['env'] == "local") {
            $this->config = parse_ini_file(ROOT_PATH . '/local.ini');
        } else {
            $application = new Application(new Config([
                'base_uri' => $bootConfig['url'],
                'guzzle_config' => [
                    'headers' => [
                        'charset' => 'UTF-8',
                    ],
                ],
            ]));
      /*      $application->auth->login($bootConfig['user'], $bootConfig['password']);;*/
            $response = $application->config->get('mico_core_service', 'V2SYSTEM_GROUP');
            $this->coreConfig = parse_ini_string((string)$response->getBody());
            $application = new Application(new Config([
                'base_uri' => $bootConfig['url'],
                'guzzle_config' => [
                    'headers' => [
                        'charset' => 'UTF-8',
                    ],
                ],
            ]));
  //          $application->auth->login($bootConfig['user'], $bootConfig['password']);
            $response = $application->config->get($bootConfig['dataId'], $bootConfig['group']);
            $this->config = parse_ini_string((string)$response->getBody());

            $register = new ServiceRegister();
            $register->handle("register");

            $process = new Swoole\Process(function ($worker) use ($register) {

                    swoole_timer_tick(5000,function() use ($register){
                        try{
                            $register->beat();
                            echo "注册中心 心跳检测～";
                        }catch (\Throwable $e){
                            //var_dump($e);
                        }

                    });

            }, false, 0, true);
            $process->start();
        }

        $this->appName = $this->config['app.name'];
        $vega = Vega::new($this->appName);
        $grpc = new \Mix\Grpc\Server();

        /* http server */
        $this->server = new Swoole\Http\Server("0.0.0.0", 8080);
        /* http server 健康检测 */
        $health = $this->server->addListener('0.0.0.0', 8081, SWOOLE_SOCK_TCP);

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
        printf("Http   Listen    Addr:       http://%s:%d\n", "0.0.0.0", "8080");
        printf("Health Listen    Addr:       http://%s:%d\n", "0.0.0.0", "8081");
        Log::info('Start http server');
        $this->server->set(array(
            'reactor_num' => swoole_cpu_num(),
            'worker_num' => $this->config['worker.num'],
            'enable_coroutine' => true,
            'max_request' => 0,
            'reload_async' => true,
            'max_wait_time' => 6
        ));

        $rabbitMq = new RabbitMqProcess($this->config, 1, $this->url, $this->tcpClient);
        $rabbitMq->handler();

        $health->on('Request', function ($request, $response) {

        $_SERVER = isset($request->server) ? $request->server : array();
            if ($_SERVER['request_uri'] != "") {
                $action = $_SERVER['request_uri'];
            }
            /* 监听 */
            self::$innerServer = new InnerServer($action, json_decode($request->rawContent(), 1), $this->config);
            $data = self::$innerServer->handler();

            if ($action == "/health/metrics") {
                $metrics = $this->server->stats();
                foreach ($metrics as $k => $v) {
                    echo sprintf("%s %s", $k, $v) . "\n";
                }
                $response->header("Content-type", "application/json;charset=utf-8");
                $response->end(datajson("10200", $metrics, "success",));
                return;
            }
            if ($action == "/health") {
                try {
                } catch (Exception $e) {
                    $this->server->shutdown();
                }
                // if ($rs) {
                $response->end("UP");
                //    } else {
                //        $this->server->shutdown();
                //          $response->end("DOWN");
                //       }
                return;
            }
            if (isset($data) && $data) {
                $response->end(datajson("10200", $data, "success",));
            } else {
                $response->end(datajson("10500", $data, "fail",));
            }

        });
        $this->server->on('open', function ($server, $request) {
        });
        $this->server->on('Start', function ($server) {

        });
        $this->server->on("ManagerStart", function ($server) {

        });
        $this->server->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->server->on('WorkerStop', function ($server, $worker_id) {
            echo "工作进程停止: " . $worker_id;
        });
        $this->server->on('WorkerExit', array($this, 'onWorkerExit'));
        $this->server->on('WorkerError', array($this, 'onWorkerError'));
        $this->server->on('Request', $vega->handler());
        $this->server->on('Receive', array($this, 'onReceive'));

    }

    public function httpStart()
    {
        $this->server->start();
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


    }

    public function onWorkerStart($server, $worker_id)
    {

        CoreDB::init($this->coreConfig);
        CoreDB::enableCoroutine();
        CoreRDS::init($this->coreConfig);
        CoreRDS::enableCoroutine();
        /*自动加载用户配置*/

        $configs = ConfigLoad::findFile($this->config["app.name"]);
        foreach ($configs as $key => $val) {
            $val::init($this->config);
            $val::enableCoroutine();

        }
    }


}