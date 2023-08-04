<?php

namespace App\Http\Controller\LocalCache;

use App\Config\HttpClient;
use App\Http\Vo\LocalCache\ReqVo;
use GuzzleHttp\Client;
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

        $time = time();
        $url  = "http://127.0.0.1:8081/healthz/readiness";
        $client = new Client();
        $fn=[];
        for ($i=0;$i<=1;$i++){
          //
        //  $result = $client->request('GET',$url,['headers' => ['Connection' => 'keep-alive','Keep-Alive'=>300]])->getBody()->getContents();
            $fn[] = function ()use ($url){
            //    print_r(HttpClient::instance()->poolStats());
             return HttpClient::instance()->request('GET',$url,['headers' => ['Connection' => 'keep-alive','Keep-Alive'=>300]])->getBody()->getContents();
            };

        }
        $response = parallel($fn,1000);
        $time1 = time()-$time;

        //var_dump($result);
        $cache = Cache::instance()->get($reqVo->key);
        Result::ok($ctx, $time1);

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