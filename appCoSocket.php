<?php
require_once __DIR__ . '/Galaxy/load.php';
require_once __DIR__ . '/service-main/src/App/load.php';
require_once __DIR__ . '/vendor/autoload.php';
use Mabang\Galaxy\Core\SocketServerCo;
date_default_timezone_set('Asia/Shanghai');
define("ROOT_PATH", dirname(__FILE__));
ini_set('display_errors', 'On');
gc_enable();
$opts = array('env:', 'user:', 'password:', 'dataId:', 'group:', 'url:', 'server.port:', 'management.server.port:', 'log.path:', 'tenant:', 'node.ip:', 'node.port:');
$bootConfig = getopt('', $opts);
if ($bootConfig['env'] == "local") {
    Logger::configure('log_config_local.xml');
}else{
    Logger::configure('log_config.xml');
}


class AppCoSocket extends SocketServerCo
{
    public function __construct()
    {

    }
}

try {
    $server = new AppCoSocket();
    $server->WebSocketStart($bootConfig);
} catch (\Throwable $ex) {
    print_r(sprintf('%s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));

}
