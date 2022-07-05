<?php


use App\Http\Controller\Helloword\Database;
use App\Http\Controller\Helloword\Helloword;
use App\Http\Controller\FeignClient\Feign;

return function (Mix\Vega\Engine $vega,$appName) {
    $sub = $vega->pathPrefix("/".$appName);
    $sub->handle('/helloword/helloword', [new Helloword(), 'helloword'])->methods('GET');
    $sub->handle('/helloword/database', [new Database(), 'databasetest'])->methods('GET');
    $sub->handle('/helloword/redis', [new Database(), 'redistest'])->methods('GET');
    $sub->handle('/helloword/Feign', [new Feign(), 'fegin1'])->methods('GET');
    $sub->handle('/helloword/Feign2', [new Feign(), 'fegin2'])->methods('GET');

    // $vega->handle('/auth', [new Auth(), 'index'])->methods('GET');
};
