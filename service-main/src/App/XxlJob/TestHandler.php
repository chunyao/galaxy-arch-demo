<?php

namespace App\XxlJob;

use Mabang\Galaxy\Common\XxlJob\Handler\BaseHandler;

class TestHandler extends BaseHandler
{
    public function handler(array $params): bool
    {
        var_dump($params);
        echo "XxlJob hello world";
        return true;
    }
}