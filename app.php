<?php
require_once __DIR__ . '/Galaxy/load.php';
require_once __DIR__ . '/service-main/src/App/load.php';
require_once __DIR__ . '/vendor/autoload.php';

date_default_timezone_set('Asia/Shanghai');
define("ROOT_PATH", dirname(__FILE__));
ini_set('display_errors', 'On');
gc_enable();
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
ini_set('error_reporting', E_ALL ^ E_NOTICE);
use Galaxy\Core\Log;
use Galaxy\Core\Server;

$opts = array('env:', 'user:', 'password:', 'dataId:', 'group:', 'url:', 'server.port:', 'management.server.port:', 'log.path:', 'tenant:', 'node.ip:', 'node.port:');
$bootConfig = getopt('', $opts);
if ($bootConfig['env'] == "local") {
    Logger::configure('log_config_local.xml');
}else{
    Logger::configure('log_config.xml');
}


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
    print_r(sprintf('%s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));

}
