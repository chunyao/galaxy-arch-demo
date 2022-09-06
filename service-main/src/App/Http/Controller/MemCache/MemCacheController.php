<?php

namespace App\Http\Controller\MemCache;

use Galaxy\Common\Configur\Cache;
use Mix\Vega\Context;
use Galaxy\Common\Memcache\Memcache;
use Galaxy\Common\Memcache\Config;

class MemCacheController
{
    public function setTest(Context $ctx)
    {

        $config = new Config();
        $config->setHost("memcached.mabangerp.com");
        $config->setPort("7101");
        $memcache = new Memcache($config);
        $memcache->connect();
        $memcache->set("abc",123,5);
        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $memcache->get("abc")
        ]);
    }

    public function getTest(Context $ctx)
    {
        $cache = Cache::instance()->get("test1");
        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $cache
        ]);
    }
}