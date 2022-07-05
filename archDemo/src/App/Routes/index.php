<?php

use App\Http\Controller\Helloword\Database;
use App\Http\Controller\Helloword\Helloword;
use App\Http\Controller\FeignClient\Feign;

return function (Mix\Vega\Engine $vega) {
    $vega->handle('/helloword/helloword', [new Helloword(), 'helloword'])->methods('GET');
    $vega->handle('/helloword/database',  [new Database(), 'databasetest'])->methods('GET');
    $vega->handle('/helloword/redis',  [new Database(), 'redistest'])->methods('GET');
    $vega->handle('/helloword/Feign',  [new Feign(), 'fegin1'])->methods('GET');
    $vega->handle('/helloword/Feign2',  [new Feign(), 'fegin2'])->methods('GET');

   // $vega->handle('/auth', [new Auth(), 'index'])->methods('GET');
};
