<?php
/**
 * Created by PhpStorm.
 * User: XUMORAN
 * Date: 2022-03-02
 * Time: 20:28
 */

namespace MabangSdk\XxlJob\Support\XxlJob\Job;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use MabangSdk\XxlJob\Facades\XxlJob;

class XxlJobCallbackHandler implements ShouldQueue
{
    public function run($params)
    {
        Log::debug('XxlJobCallbackHandler:', [$params]);
        $result  =  XxlJob::callback($params);
        return true;
    }
}