<?php

namespace Mabang\Galaxy\Common\Utils;

class GetLocalIp
{
    public static function getIp():string{
        $ip = swoole_get_local_ip();
        if (isset($ip['en0'])) {
            $localIp = $ip['en0'];
        }
        if (isset($ip['en1'])) {
            $localIp = $ip['en1'];
        }
        if (isset($ip['eth0'])) {
            $localIp = $ip['eth0'];
        }
        if (isset($ip['eth1'])) {
            $localIp = $ip['eth1'];
        }
        return $localIp;
    }

}