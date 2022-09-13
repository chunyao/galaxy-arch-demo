<?php
namespace App\Http\Controller\Spi;

use App\Spi\MabangArchDemo\Bo\HelloBo;
use App\Spi\MabangArchDemo\Service\ArchService;
use Galaxy\Core\BaseController;
use Mix\Vega\Context;


class SpiDemo extends BaseController
{
    public function callSpiDemo(Context $ctx){
        $hello = new HelloBo();
        $hello->setA("aaaa");
        $hello->setB("bbb");
        $data = ArchService::instance()->HelloWord($hello);

        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => 1
        ]);
    }
}