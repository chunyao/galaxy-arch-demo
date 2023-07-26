<?php

use Mabang\Galaxy\Core\Container;
use Mabang\Galaxy\Http\Controller\CoreServer;

return function (Mix\Vega\Engine $vega) {
    $container = new Container();
    $controller = $container->get(CoreServer::class);
    $vega->handle('/healthz/readiness',null,null, [$controller, 'health'])->methods('GET');
    $vega->handle('/healthz/metrics', null,null,[$controller, 'metrics'])->methods('GET');
    $vega->handle('/rabbitmq',null,null, [$controller, 'innerBus'])->methods('POST');
    $vega->handle('/redis',null,null, [$controller, 'innerBus'])->methods('POST');
};