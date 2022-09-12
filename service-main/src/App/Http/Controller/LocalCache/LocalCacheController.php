<?php

namespace App\Http\Controller\LocalCache;

use Galaxy\Common\Configur\Cache;
use Mix\Vega\Context;

class LocalCacheController
{
    public function setTest(Context $ctx)
    {
        $data = array();
        /*orderStatus:3
tableBase:2
isCloud:2
companyId:100001
selectCase:count(0) as id
WS_REQUEST_API:1
a:findLeftJoinTables
m:Order*/
        $data['tableBase'] = 2;
        $data['isCloud'] = 2;
        $data['companyId'] = 100001;
        $data['selectCase'] = 'count(0) as id';
        $data['orderStatus'] = 3;
        $data['WS_REQUEST_API'] = 1;
        $data['a'] = 'findLeftJoinTables';
        $data['m'] = 'Order';
        httpRequest("http://webservice.order.mabangerp.com/index.php", $data);
        $return = Cache::instance()->set("test1", "123", 30);
        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $return
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

    public function deleteTest()
    {

    }
}