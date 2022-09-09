<?php
namespace App\Http\Controller\LocalCache;
use Galaxy\Common\Configur\Cache;
use Mix\Vega\Context;

class LocalCacheController
{
    public function setTest(Context $ctx){
        $return = Cache::instance()->set("test1","123",6000);
        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $return
        ]);
    }

    public function getTest(Context $ctx){
        $cache =Cache::instance()->get("test1");
        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $cache
        ]);
    }
    public function deleteTest(){

    }
}