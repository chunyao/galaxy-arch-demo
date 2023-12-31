<?php

namespace Mabang\Galaxy\Core;



use Mabang\Galaxy\Common\Utils\GetLocalIp;

class ServiceRegister
{
    private string $serviceName;

    private string $namespaceId;

    private string $ip;

    private string $host;

    public function __construct($host, $serviceName, $namespaceId)
    {
        $this->host=$host;
        $this->ip = GetLocalIp::getIp();
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