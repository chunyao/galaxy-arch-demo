<?php

use Galaxy\Http\Controller\CoreServer;

return function (Mix\Vega\Engine $vega) {
    $vega->handle('/healthz/readiness', [new CoreServer(), 'health'])->methods('GET');
    $vega->handle('/healthz/metrics', [new CoreServer(), 'metrics'])->methods('GET');
    $vega->handle('/rabbitmq', [new CoreServer(), 'innerBus'])->methods('POST');

};
