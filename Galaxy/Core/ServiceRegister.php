<?php

namespace Galaxy\Core;

use alibaba\nacos\NacosConfig;
use alibaba\nacos\Naming;


class ServiceRegister
{
    private string $host;

    private string $serviceName;

    private string $namespaceId;

    private string $ip;

    public function __construct($host, $serviceName, $namespaceId)
    {

        $ip = swoole_get_local_ip();
        if (isset($ip['en0'])) {
            $this->ip = $ip['en0'];
        }
        if (isset($ip['eth0'])) {
            $this->ip = $ip['eth0'];
        }
        $this->host = $host;
        $this->serviceName = $serviceName;
        $this->namespaceId = $namespaceId;
    }

    public function handle($action)
    {
        switch ($action) {
            case 'register':
                $result = $this->register();
                break;

        }
    }

    public function beat()
    {
        $result = $this->register();
    }

    private function register()
    {
        rest_curl($this->host . '/nacos/v1/ns/instance?port=8080&ephemeral=true&healthy=true&ip=' . $this->ip . '&weight=1.0&serviceName=' . $this->serviceName . '&encoding=GBK&namespaceId=' . $this->namespaceId, 'POST');

    }
}