<?php
namespace App\Http\Controller\Spi;

use App\MabangArchDemo\Spi\Bo\HelloBo;
use App\MabangArchDemo\Spi\Service\ArchService;
use Galaxy\Core\BaseController;
use Mix\Vega\Context;
use Swoole\Coroutine as co;

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