<?php

namespace App\Http\Controller\LocalCache;

use App\Http\Vo\LocalCache\ReqVo;
use Mabang\Galaxy\Common\Configur\Cache;
use Mabang\Galaxy\Http\Vo\Result;
use Mix\Vega\Context;
use Mabang\Galaxy\Common\Annotation\Route;

class LocalCacheController
{
    /**
     * @Autowired()
     */

    /**
     * @Route(route="/cache/set",method="POST",contextType="JSON",param="\App\Http\Vo\LocalCache\ReqVo")
     */
    public function setTest(Context $ctx, ReqVo $reqVo)
    {
        $return = Cache::instance()->set($reqVo->key, $reqVo->val);
        Result::ok($ctx, $return);
    }

    /**
     * @Route(route="/cache/set2",method="POST",contextType="FORM",param="\App\Http\Vo\LocalCache\ReqVo")
     */
    public function setTest2(Context $ctx, ReqVo $reqVo)
    {
        $return = Cache::instance()->set($reqVo->key, $reqVo->val);
        Result::ok($ctx, $return);
    }

    /**
     * @Route(route="/cache/get",method="GET",contextType="QUERY",param="\App\Http\Vo\LocalCache\ReqVo")
     */
    public function getTest(Context $ctx, ReqVo $reqVo)
    {

        $cache = Cache::instance()->get($reqVo->key);
        Result::ok($ctx, $cache);

    }

    /**
     * @Route(route="/cache/{key:\d+}",method="GET",contextType="REGEX",param="\App\Http\Vo\LocalCache\ReqVo")
     */
    public function getTestByRegex(Context $ctx, ReqVo $reqVo)
    {
        $cache = Cache::instance()->get($reqVo->key);
        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $cache
        ]);
    }

    public function deleteTest()
    {

    }
}