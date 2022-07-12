<?php


use App\Http\Controller\Helloword\Database;
use App\Http\Controller\Helloword\Helloword;
use App\Http\Controller\Helloword\SendMsg;

return function (Mix\Vega\Engine $vega,$appName) {
    $sub = $vega->pathPrefix("/".$appName);

    $sub->handle('/helloword/helloword', [new Helloword(), 'helloword'])->methods('GET');
    $sub->handle('/helloword/database', [new Database(), 'databasetest'])->methods('GET');
    $sub->handle('/helloword/redis', [new Database(), 'redistest'])->methods('GET');
    $sub->handle('/msg/send', [new SendMsg(), 'handler'])->methods('GET');
    $sub->handle('/msg/send2', [new SendMsg(), 'send'])->methods('POST');


    // $vega->handle('/auth', [new Auth(), 'index'])->methods('GET');
};
