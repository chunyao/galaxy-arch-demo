<?php

namespace App\Spi\MabangArchDemo\Service;


use App\Spi\MabangArchDemo\Bo\HelloBo;

class ArchService extends BaseService
{

    /*调用 mabang-arch-demo hello word*/

    public function HelloWord(HelloBo $helloBo){
        /*固定方法*/
        $arr = require
        $path = '/helloword/helloword';
        return parent::call($path,$helloBo,'GET');
    }
}