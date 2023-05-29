<?php

namespace App\Http\Controller\MemCache;

use App\Config\MC;
use Mabang\Galaxy\Common\Configur\Cache;
use Mix\Vega\Context;
use Mabang\Galaxy\Common\Memcache\Memcache;
use Mabang\Galaxy\Common\Memcache\Config;

class MemCacheController
{
    public function setTest(Context $ctx)
    {


        MC::instance()->set("abc",123,300);
        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => MC::instance()->get("abc")
        ]);
    }

    public function getTest(Context $ctx)
    {
        $cache = MC::instance()->get("abc");
        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $cache
        ]);
    }
}