<?php
declare(strict_types=1);

namespace App\Http\Controller\Helloword;


use App\Service\SayService;
use Galaxy\Core\BaseController;
use Galaxy\Core\Log;
use App\Service\MsgService;
use Mix\Vega\Context;

class Helloword extends BaseController
{
    private MsgService $msgSevice;
    private SayService $sayService;

    public function __construct()
    {
        $this->msgSevice = new MsgService();
        $this->sayService = new SayService();
    }

    public function helloword(Context $ctx)
    {

        $id = rand(1, 239368);
        $data = $this->msgSevice->findById($id);

        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $data
        ]);

        $echo_string = datajson(10200, $data, "success", $cache = false);
        /* 写法1*/
        //  $ctx->string(200, $echo_string);
        /* 写法2*/
        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $data
        ]);

    }

    public function __destruct()
    {
        unset($this->msgModel);
    }

}



