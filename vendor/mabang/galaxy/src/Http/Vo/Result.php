<?php

namespace Mabang\Galaxy\Http\Vo;

use Mix\Http\Message\Stream\StringStream;
use Mix\Vega\Context;

class Result
{
    public static function ok(Context $ctx, $data)
    {
        return $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $data
        ]);
    }

    public static function fail(Context $ctx, $data)
    {
        return $ctx->JSON(200, [
            'code' => 10500,
            'message' => 'fail',
            'data' => $data
        ]);
    }
}