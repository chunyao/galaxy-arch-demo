<?php

namespace Galaxy\Common\Mq;


interface ConnectionInterface
{
    public function publish($messageBody, $exchange, $routeKey, $head = [], $ack = 0, $retry = 0): int;


}
