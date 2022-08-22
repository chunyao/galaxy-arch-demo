<?php
use App\Http\Controller\Helloword\Database;
use App\Http\Controller\Helloword\Helloword;
use App\Http\Controller\Helloword\SendMsg;
use App\Http\Controller\Msg\Msg;
use App\Http\Controller\Sql\SqlImprove;
use App\Http\Controller\Es\Index;
return function (Mix\Vega\Engine $vega,$appName) {
    $sub = $vega->pathPrefix("/".$appName);

    $sub->handle('/helloword/helloword', [new Helloword(), 'helloword'])->methods('GET');
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

    // $vega->handle('/auth', [new Auth(), 'index'])->methods('GET');
};
