<?php
use App\Http\Controller\Helloword\Database;
use App\Http\Controller\Helloword\Helloword;
use App\Http\Controller\Helloword\SendMsg;

use App\Http\Controller\Helloword\WebSocket;
use App\Http\Controller\LocalCache\LocalCacheController;
use App\Http\Controller\MemCache\MemCacheController;
use App\Http\Controller\Msg\Msg;
use App\Http\Controller\Spi\SpiDemo;
use App\Http\Controller\Sql\SqlImprove;
use App\Http\Controller\Es\Index;


return function (Mix\Vega\Engine $vega,$appName) {

    $sub = $vega->pathPrefix("/" . $appName);
    //全局切面， 类似lavarel 中间件
    $sub->use(function (Mix\Vega\Context $ctx) {

        $ctx->next();
    });
    $sub->handle('/helloword/helloword', [new Helloword(), 'helloword'])->methods('GET');
    $sub->handle('/helloword/co', [new Helloword(), 'co'])->methods('GET');
    $sub->handle('/ht', [new Helloword(), 'ht'])->methods('GET');
    $sub->handle('/helloword/database', [new Database(), 'databasetest'])->methods('GET');
    $sub->handle('/helloword/redis', [new Database(), 'redistest'])->methods('GET');
    $sub->handle('/msg/send', [new SendMsg(), 'handler'])->methods('GET');
    $sub->handle('/msg/send2', [new SendMsg(), 'send'])->methods('POST');
    $sub->handle('/mg/in', [new Database(), 'redistest'])->methods('GET');
    $sub->handle('/sql/1', [new SqlImprove(), 'sql1'])->methods('GET');
    $sub->handle('/es/createIndex', [new Index(), 'createIndex'])->methods('GET');
    $sub->handle('/es/getIndexData', [new Index(), 'getDataByIndex'])->methods('GET');
    $sub->handle('/msg/handler', [new Msg(), 'handler'])->methods('GET');
    $sub->handle('/file/handler', [new Helloword(), 'upload'])->methods('GET');
    $sub->handle('/snow', [new Helloword(), 'snow'])->methods('GET');
    $sub->handle('/cache/set', [new LocalCacheController(), 'setTest'])->methods('GET');
    $sub->handle('/cache/get', [new LocalCacheController(), 'getTest'])->methods('GET');
    $sub->handle('/mem/set', [new MemCacheController(), 'setTest'])->methods('GET');
    $sub->handle('/mem/get', [new MemCacheController(), 'getTest'])->methods('GET');
    $sub->handle('/spi/test', [new SpiDemo(), 'callSpiDemo'])->methods('GET');
    $sub->handle('/gethtml', [new Helloword(), 'gethtml'])->methods('GET');
    //ws
    $sub->handle('/ws/hello', [new WebSocket(), 'index'])->methods('GET');

    // $vega->handle('/auth', [new Auth(), 'index'])->methods('GET');
};
