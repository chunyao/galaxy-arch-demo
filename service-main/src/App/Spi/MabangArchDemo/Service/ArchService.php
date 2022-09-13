<?php

namespace App\MabangArchDemo\Spi\Service;
use App\MabangArchDemo\Spi\Bo\HelloBo;

class ArchService extends BaseService
{

    /*调用 mabang-arch-demo hello word*/

    public function HelloWord(HelloBo $helloBo){
        /*固定方法*/
        $path = '/helloword/helloword';
        return parent::call($path,$helloBo);
    }
}