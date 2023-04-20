<?php

namespace App\Service;

use Swoole\Runtime;

class CoDemoService
{

    public function iniProcess($data){
        $callback = [];
        foreach ($data as $key=>$item){
            $callback[$key] = $this->process($item);
        }
        //10个协程
        Runtime::enableCoroutine(SWOOLE_HOOK_NATIVE_CURL);
        $result = parallel($callback,10);
        //result 中key 是$data里的key
        return $result;

    }
    private function process($item)
    {
        return function () use($item){
            /* 写 程序*/
            /*
             * 使用 这个 client
            $data = (string)(new \GuzzleHttp\Client())->request('POST', $req, ['timeout' => 120, 'json' => $msgBody])->getBody();
            // $resp = json_decode($data->body);
            $resp = json_decode($data);
            */

            $result = $item['result'];
            return $result;
        };
    }
}