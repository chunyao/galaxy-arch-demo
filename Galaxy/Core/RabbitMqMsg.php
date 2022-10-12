<?php

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
            $msgBody['message'] = $tmp;
            $msgBody['messageId']=$tmp['messageId'];

            $msgBody['queue'] = App::$innerConfig['rabbitmq.queue'][$i];
            $msgBody['type'] = "mq";
            unset($tmp);
            Log::info(sprintf('messageId: %s queue: %s', $msgBody['messageId'], $msgBody['queue']));
            // $resp = json_decode((string)rest_post( $this->url,$msgBody,3));
            if (isset(APP::$localcache[$msgBody['messageId']])) {
                if (APP::$localcache[$msgBody['messageId']] >= 3 ) {
                    Log::info(sprintf('重试: ' . APP::$localcache[$msgBody['messageId']] . ' messageId ack : %s 进程Id %s', $msgBody['messageId'], posix_getpid()));
                    try {
                        $msg->delivery_info["channel"]->basic_reject($msg->delivery_info["delivery_tag"], false);
                    }catch (\Throwable $ex){
                        Log::error(json_encode($msg));
                        Log::error(sprintf('ack: %s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
                    }
                    unset(APP::$localcache[$msgBody['messageId']]);
                    return;
                }
                try {

                    $data = (string)self::$httpClient->request('POST',$req, ['json' => $msgBody, 'curl' => [
                        CURLOPT_UNIX_SOCKET_PATH => ROOT_PATH.'/myserv.sock'
                    ]])->getBody();
                    $resp = json_decode($data);
                    if ($resp->code === 10200) {
                        $msg->delivery_info["channel"]->basic_ack($msg->delivery_info["delivery_tag"]);
                        Log::info(sprintf('messageId ack : %s', $msgBody['messageId']));
                        unset(APP::$localcache[$msgBody['messageId']]);
                        return;
                    }
                } catch (\Throwable $ex) {
                    Log::error(sprintf('ack: %s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
                }
                APP::$localcache[$msgBody['messageId']]++;
                $msg->delivery_info["channel"]->basic_reject($msg->delivery_info["delivery_tag"],true);
                Log::error(sprintf('重试: ' . APP::$localcache[$msgBody['messageId']] . ' messageId ack : %s 进程 %s', $msgBody['messageId'], posix_getpid()));
            } else {
                try {

                    $data = (string)(new GuzzleHttp\Client())->request('POST',$req, ['json' => $msgBody, 'curl' => [
                        CURLOPT_UNIX_SOCKET_PATH => ROOT_PATH.'/myserv.sock'
                    ]])->getBody();

                    $resp = json_decode($data);
                    if ($resp->code === 10200) {
                        $msg->delivery_info["channel"]->basic_ack($msg->delivery_info["delivery_tag"]);
                        Log::info(sprintf('messageId ack : %s', $msgBody['messageId']));
                        unset(APP::$localcache[$msgBody['messageId']]);
                        return;
                    }
                } catch (\Throwable $ex) {
                    Log::error(sprintf('ack: %s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
                }
                APP::$localcache[$msgBody['messageId']] = 1;
                $msg->delivery_info["channel"]->basic_reject($msg->delivery_info["delivery_tag"],true);
                Log::error(sprintf('重试: ' . APP::$localcache[$msgBody['messageId']] . ' messageId unack : %s queue: %s', $msgBody['messageId'], $msgBody['queue']));
            }

            // 响应ack
        };