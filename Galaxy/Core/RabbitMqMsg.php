<?php

use Galaxy\Common\Configur\Cache;
use Galaxy\Core\Log;

return static function ($msg) use ($i, $msgBody,$req,$num) {

            /*if (isset($this->config['rabbitmq.qps'][$i])) {
                $sleep = round(1000000 / ((int)$this->config['rabbitmq.qps'][$i]));
                usleep($sleep);
            }*/
            //  sleep(30);
            /*冷启动*/

            $tmp = json_decode($msg->body, true);
            $tmp['queue'] = App::$innerConfig['rabbitmq.queue'][$i];

            if (isset($tmp['id'])) {
                $tmp['messageId'] = $tmp['id'];
            }
            if(empty($tmp['messageId'])) {
                Log::error("messageId 为空");
                $msg->delivery_info["channel"]->basic_ack($msg->delivery_info["delivery_tag"]);
                return ;}
            $msgBody['message'] = $tmp;
            $msgBody['messageId']=$tmp['messageId'];

            $msgBody['queue'] = App::$innerConfig['rabbitmq.queue'][$i];
            $msgBody['type'] = "mq";
            unset($tmp);
            Log::info(sprintf('messageId: %s queue: %s', $msgBody['messageId'], $msgBody['queue']));

            // $resp = json_decode((string)rest_post( $this->url,$msgBody,3));
            if (Cache::instance()->getIncr($msgBody['messageId'])!==null) {
                echo Cache::instance()->getIncr($msgBody['messageId']);
                if (((int)Cache::instance()->getIncr($msgBody['messageId'])) >= 3 ) {
                    Log::info(sprintf('重试: ' .Cache::instance()->getIncr($msgBody['messageId']) . ' messageId 丢弃 : %s 进程Id %s', $msgBody['messageId'], posix_getpid()));
                    try {
                        $msg->delivery_info["channel"]->basic_reject($msg->delivery_info["delivery_tag"], false);
                    }catch (\Throwable $ex){
                        Log::error(json_encode($msg));
                        Log::error(sprintf('ack: %s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
                    }
                    Cache::instance()->del($msgBody['messageId']);
                    return;
                }
                try {
                    $data = (string)self::$httpClient->request('POST',$req, ['json' => $msgBody])->getBody();
                    $resp = json_decode($data);
                    if ($resp->code === 10200) {
                        $msg->delivery_info["channel"]->basic_ack($msg->delivery_info["delivery_tag"]);
                        Log::info(sprintf('messageId ack : %s', $msgBody['messageId']));
                        Cache::instance()->del($msgBody['messageId']);
                        return;
                    }
                } catch (\Throwable $ex) {
                    Log::error(sprintf('ack: %s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
                }
                Cache::instance()->incr($msgBody['messageId']);
                $msg->delivery_info["channel"]->basic_reject($msg->delivery_info["delivery_tag"],true);
                Log::error(sprintf('重试: ' .Cache::instance()->getIncr($msgBody['messageId']). ' messageId basic_reject : %s 进程 %s', $msgBody['messageId'], posix_getpid()));
            } else {
                try {
                    $data = (string)(new GuzzleHttp\Client())->request('POST',$req, ['json' => $msgBody])->getBody();
                    $resp = json_decode($data);
                    if ($resp->code === 10200) {
                        $msg->delivery_info["channel"]->basic_ack($msg->delivery_info["delivery_tag"]);
                        Log::info(sprintf('messageId ack : %s', $msgBody['messageId']));
                        return;
                    }
                } catch (\Throwable $ex) {
                    Log::error(sprintf('ack: %s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
                }
                Cache::instance()->setIncr($msgBody['messageId']);
                $msg->delivery_info["channel"]->basic_reject($msg->delivery_info["delivery_tag"],true);
                Log::error(sprintf('重试: ' . $msgBody['messageId'] . ' messageId unack : %s queue: %s', $msgBody['messageId'], $msgBody['queue']));
            }

            // 响应ack
        };