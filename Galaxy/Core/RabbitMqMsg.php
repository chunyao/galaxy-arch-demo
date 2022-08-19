<?php

use Galaxy\Core\Log;

return function ($msg) use ($i, $msgBody) {
     if (isset($this->config['rabbitmq.qps'][$i])) {
         $sleep = round(1000000 / ((int)$this->config['rabbitmq.qps'][$i]));
         usleep($sleep);
     }
    //  sleep(30);
    /*冷启动*/

    $tmp = json_decode($msg->body, true);
    $tmp['queue'] = $this->config['rabbitmq.queue'][$i];
    if (isset($tmp['id'])) {
        $tmp['messageId'] = $tmp['id'];
    }
    $msgBody['message'] = $tmp;

    Log::info(sprintf('messageId: %s queue: %s', $tmp['messageId'], $tmp['queue']));
    $msgBody['queue'] = $this->config['rabbitmq.queue'][$i];
    $msgBody['type'] = "mq";

    // $resp = json_decode((string)rest_post( $this->url,$msgBody,3));
    if (isset(APP::$localcache[$tmp['messageId']])) {
        if (APP::$localcache[$tmp['messageId']] > 3) {
            Log::error(sprintf('重试: ' . APP::$localcache[$tmp['messageId']] . ' messageId ack : %s 进程Id %s', $tmp['messageId'], posix_getpid()));
            $msg->delivery_info["channel"]->basic_reject($msg->delivery_info["delivery_tag"], false);
            unset(APP::$localcache[$tmp['messageId']]);
            return;
        }
        try {
            $data = (string)self::$httpClient->request('POST', $this->url, ['json' => $msgBody])->getBody();
            $resp = json_decode($data);
            if ($resp->code === 10200) {
                $msg->delivery_info["channel"]->basic_ack($msg->delivery_info["delivery_tag"]);
                //     Log::info(sprintf('messageId ack : %s', $tmp['messageId']));
                unset(APP::$localcache[$tmp['messageId']]);
                return;
            }
        } catch (\Throwable $ex) {

            Log::error(sprintf('ack: %s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));

        }
        APP::$localcache[$tmp['messageId']]++;
        $msg->delivery_info["channel"]->basic_recover(true);
        Log::error(sprintf('重试: ' . APP::$localcache[$tmp['messageId']] . ' messageId ack : %s 进程 %s', $tmp['messageId'], posix_getpid()));
    } else {
        try {
            $data = (string)self::$httpClient->request('POST', $this->url, ['json' => $msgBody])->getBody();
            $resp = json_decode($data);
            if ($resp->code === 10200) {
                $msg->delivery_info["channel"]->basic_ack($msg->delivery_info["delivery_tag"]);
                //   Log::info(sprintf('messageId ack : %s', $tmp['messageId']));
                unset(APP::$localcache[$tmp['messageId']]);
                return;
            }
        } catch (\Throwable $ex) {

            Log::error(sprintf('ack: %s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));

        }
        APP::$localcache[$tmp['messageId']] = 1;
        $msg->delivery_info["channel"]->basic_recover(true);
        Log::error(sprintf('重试: ' . APP::$localcache[$tmp['messageId']] . ' messageId unack : %s queue: %s', $tmp['messageId'], $msgBody['queue']));
    }
    // 响应ack
};