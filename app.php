<?php
require_once __DIR__ . '/Galaxy/load.php';
require_once __DIR__ . '/service-main/src/App/load.php';
require_once __DIR__ . '/vendor/autoload.php';

ini_set('date.timezone', 'Asia/Shanghai');
define("ROOT_PATH", dirname(__FILE__));
ini_set('display_errors', 'On');
gc_enable();

use Galaxy\Core\Log;
use Galaxy\Core\Server;

$opts = array('env:', 'user:', 'password:', 'dataId:', 'group:', 'url:', 'server.port:', 'management.server.port:', 'log.path:', 'tenant:', 'node.ip:', 'node.port:');
$bootConfig = getopt('', $opts);
if ($bootConfig['env'] == "local") {
    SeasLog::setBasePath("./data/logs");
}
if (isset($bootConfig['log.path'])) {
    SeasLog::setBasePath($bootConfig['log.path']);
}
$ip = swoole_get_local_ip();
if (isset($ip['en0'])) {
    $ip = $ip['en0'];
}
if (isset($ip['eth0'])) {
    $ip = $ip['eth0'];
}
$newip = str_replace(".","_",$ip);

SeasLog::setLogger("/mabang-arch-demo");
SeasLog::setFilePrefix($newip."-");
class App extends Server
{
    public function __construct($bootConfig)
    {
        parent:: __construct($bootConfig);
    }
}

try {
    $server = new App($bootConfig);
    $server->httpStart();
} catch (\Throwable $ex) {
    Log::error(sprintf('%s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
    //var_dump($e);
}
