<?php
declare(strict_types=1);

namespace App\Http\Controller\Helloword;


use App\Repository\Model\Mongo\Product;
use App\Service\SayService;
use App\Service\WishbrandService;
use Galaxy\Core\BaseController;
use Galaxy\Core\Log;
use App\Service\MsgService;
use Mix\Vega\Context;

class Helloword extends BaseController
{
    private MsgService $msgSevice;
    private SayService $sayService;
    private Product $product;
    private WishbrandService $wishbrandService;
    public function __construct()
    {
        $this->product = new Product();
        $this->msgSevice = new MsgService();
        $this->wishbrandService = new WishbrandService();
    }

    public function helloword(Context $ctx)
    {   /*mongo*/
        $data = $this->product->selectDataById();
       // $data = $this->wishbrandService->findById(522);
        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $data
        ]);
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



