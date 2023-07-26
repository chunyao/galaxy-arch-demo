<?php

namespace Mabang\Galaxy\Http\Controller;

use Mabang\Galaxy\Common\Handler\InnerServer;
use Mix\Vega\Context;

class XxlJobExecutor
{
    public function beat(Context $ctx)
    {
        $ctx->JSON(200, [
            'code' => 200,
            'msg' => null,
            'data' => true
        ]);
    }
}