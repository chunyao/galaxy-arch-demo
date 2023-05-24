<?php

namespace Galaxy\Http\Middleware\All;


use Galaxy\Core\Log;
use Mix\Vega\Context;

class TraceRecordMiddleware
{
    public function handle(Context $ctx)
    {

        Log::info(["请求"=>[
            'traceId' => $this->skywalking_trace_id(),
            'path' => $ctx->uri()->getPath().'?'.$ctx->uri()->getQuery(),
            'request' =>  $ctx->getJSON(),
            'respose' =>  json_decode($ctx->response->getBody()->getContents(),1),
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