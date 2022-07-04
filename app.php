<?php
require_once __DIR__ . '/archDemo/src/App/load.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Galaxy/load.php';
ini_set('date.timezone', 'Asia/Shanghai');
define("ROOT_PATH", dirname(__FILE__));
ini_set('display_errors', 'On');
SeasLog::setBasePath("./data/logs");
SeasLog::setLogger("/mabang-arch-demo");



use Galaxy\Core\Server;
use Galaxy\Core\PoolServer;
$opts = array('env:','user:','password:','dataId:','group:','url:');

$bootConfig = getopt('', $opts);

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
 }catch (\Throwable $e){
    var_dump($e);
}
