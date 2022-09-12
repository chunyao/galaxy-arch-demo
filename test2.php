<?php
function is_cli(){
       return preg_match("/cli/i", php_sapi_name()) ? true : false;
 }

date_default_timezone_set("PRC");
echo date("Y-m-d H:i:s");
$memObj = new Memcache();

//连接memcache服务器 参数 地址,端口（memcache的默认端口为 11211）
function heartbeat (&$obj){
    $obj->set("heartbeat",123);
    echo $obj->get("heartbeat");
    return;
}
$memObj->connect('memcached.mabangerp.com', 7101);
$memObj->set("test","123");
echo $memObj->get("test");

$pids = array();
$pids[0] = pcntl_fork();
switch ($pids[0]) {
    case -1:
        echo "fork error : 0 \r\n";
        exit;
    case 0:
        echo "心跳".$pids[0];
        while (1){

            sleep(30);
            heartbeat($memObj);
        }

        exit;
    default:
        break;
}
echo strpos(php_sapi_name(), 'cli');
//echo $memObj->get("test");
?>