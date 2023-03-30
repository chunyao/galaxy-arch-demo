<?php

namespace Galaxy\Core;

use Galaxy\Common\Configur\Cache;
use Galaxy\Common\Configur\SnowFlake;
use Galaxy\Common\Configur\Upgrader;
use \Swoole;
use \Hyperf\Nacos\Application;
use \Hyperf\Nacos\Config;
use \GuzzleHttp;

class SocketServerCo
{
    protected array $config;

    public static array $localcache;

    public static array $innerConfig;

    public static string $trackId;

    public static array $bootConfig;
    public static \GuzzleHttp\Client $httpClient;

    public static string $appName;

    public function WebSocketStart($bootConfig)
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

        }


        self::$appName = $this->config['app.name'];

        Swoole\Coroutine\run(function () {
            SnowFlake::init();
            Upgrader::init();
            Upgrader::enableCoroutine();
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

            $vega = Vega::new(self::$appName);
            $host = '0.0.0.0';
            $port = 8080;
            $server = new Swoole\Coroutine\Http\Server($host, $port, false, false);
            $server->handle('/', $vega->handler());

            foreach ([SIGHUP, SIGINT, SIGTERM] as $signal) {
                Swoole\Process::signal($signal, function () use ($server) {
                    Log::info('Shutdown swoole coroutine server');
                    $server->shutdown();
                    Shutdown::trigger();
                });
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
            printf("Listen    Addr:       http://%s:%d\n", $host, $port);
            Log::info('Start swoole coroutine server');

            $server->start();

        });
    }
}