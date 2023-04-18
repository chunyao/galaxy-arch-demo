<?php

namespace  Galaxy\Common\Mq\Channel;



use PhpAmqpLib\Channel\AMQPChannel;

interface ConnectionInterface
{
    public function obj(): AMQPChannel;
    public function publish($messageBody, $exchange, $routeKey, $head = [], $ack = 0, $retry = 0): int;
}
