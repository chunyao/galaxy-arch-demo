<?php

namespace App\Http\Controller\Sql;


use App\Service\ImproveSqlService;
use Mix\Vega\Context;

class SqlImprove
{


    public function __construct() {


    }
    public function sql1(Context $ctx)
    {
        $data = ImproveSqlService::instance()->improveSql();

        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $data
        ]);
    }
}