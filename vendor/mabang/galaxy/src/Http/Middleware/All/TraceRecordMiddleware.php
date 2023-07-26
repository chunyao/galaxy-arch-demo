<?php

namespace  Mabang\Galaxy\Http\Middleware\All;


use Mabang\Galaxy\Core\Log;
use Mix\Vega\Context;

class TraceRecordMiddleware
{
    public function before(Context $ctx)
    {

        Log::info(["请求"=>[
            'traceId' => $this->skywalking_trace_id(),
            'path' => $ctx->uri()->getPath().'?'.$ctx->uri()->getQuery(),
            'request' =>  $ctx->getJSON(),
            'status'=>$ctx->response->getStatusCode()
        ]]);


    }
    public function after(Context $ctx)
    {

        Log::info(["返回"=>[
            'traceId' => $this->skywalking_trace_id(),
            'path' => $ctx->uri()->getPath().'?'.$ctx->uri()->getQuery(),
            'respose' =>  json_decode($ctx->response->getBody(),1),
            'status'=>$ctx->response->getStatusCode()
        ]]);


    }

    private function skywalking_trace_id()
    {
        if (function_exists('skywalking_trace_id')) {
            return empty(skywalking_trace_id()) ? uniqid() : skywalking_trace_id();
        } else {
            return uniqid();
        }
    }
}