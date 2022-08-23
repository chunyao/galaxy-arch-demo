<?php

namespace App\XxlJob;

use Galaxy\Common\XxlJob\Handler\BaseHandler;

class TestHandler extends BaseHandler
{
    public function handler(): bool
    {
        echo "XxlJob hello world";
        return true;
    }
}