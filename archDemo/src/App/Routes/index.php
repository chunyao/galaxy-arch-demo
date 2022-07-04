<?php

use App\Http\Controller\Helloword\Database;
use App\Http\Controller\Helloword\Helloword;


return function (Mix\Vega\Engine $vega) {
    $vega->handle('/helloword/helloword', [new Helloword(), 'helloword'])->methods('GET');
    $vega->handle('/helloword/database',  [new Database(), 'databasetest'])->methods('GET');
   // $vega->handle('/auth', [new Auth(), 'index'])->methods('GET');
};
