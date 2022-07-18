<?php
namespace App\Http\Controller\Es;
use App\Config\ES;
use App\Service\ImproveSqlService;
use Mix\Vega\Context;

class Index
{
    public function createIndex(Context $ctx){
        ES::instance()->setIndex("stock")->createIndex();
        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => "1"
        ]);
    }
    public function sycncDataByIndex(Context $ctx){
        ImproveSqlService::instance()->sycncByCompanyId();
        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => "1"
        ]);
    }
    public function getDataByIndex(Context $ctx){
        $data = ES::instance()->setIndex("stock")->getIndexData();
        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $data
        ]);
    }
}