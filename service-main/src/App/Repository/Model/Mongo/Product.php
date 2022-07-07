<?php

namespace App\Repository\Model\Mongo;

use App\Config\MG;

class Product
{
    private string $table;

    public function __construct()
    {
        $this->table = 'msg';
    }
    public function selectDataById(){
       $data = MG::instance()->database("mdc_product_online")->table('tb_product')->find(['productId'=>"2814182186"]);
       var_dump($data);
        return $data;
    }
}
