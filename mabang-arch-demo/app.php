<?php
require_once __DIR__ . '/../Galaxy/load.php';
require_once __DIR__ . '/src/App/load.php';
require_once __DIR__ . '/../vendor/autoload.php';

ini_set('date.timezone', 'Asia/Shanghai');
define("ROOT_PATH", dirname(__FILE__));
ini_set('display_errors', 'On');
SeasLog::setLogger("/mabang-arch-demo");

use Galaxy\Core\Log;
use Galaxy\Core\Server;
use Galaxy\Core\PoolServer;
$opts = array('env:','user:','password:','dataId:','group:','url:','server.port','management.server.port');

$bootConfig = getopt('', $opts);
$applicationAspectKernel = \pheign\kernel\PheignKernel::getInstance();
$applicationAspectKernel->init(array(
    'debug' => true,
    'appDir' => __DIR__ . '/src/App/Service', // The directory where you find your request class
    'cacheDir' => __DIR__ . '/../cache',
    'excludePaths' => array(
        __DIR__ . '/../vendor'
    )
));
class App extends Server
{
    public function __construct($bootConfig){
        parent:: __construct($bootConfig);
    }
}
class Pool extends PoolServer
{
    public function __construct($bootConfig){
        parent:: __construct($bootConfig);
    }
}
//$server = new Pool($bootConfig);
//$server->poolStart();
try {
    $server = new App($bootConfig);
    $server->httpStart();
 }catch (\Throwable $ex){
    Log::error(sprintf('%s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
    //var_dump($e);
}
