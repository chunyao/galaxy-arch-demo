<?php

namespace App\Service;


use App\Config\Suffix;
use App\Repository\Model\WishbrandModel;
use  Mabang\Galaxy\Common\Utils\SnowFlakeUtils;
use App\Repository\Model\ES\Test;
class WishbrandService
{
    private WishbrandModel $wishbrandModel;
    private Test $es;
    public function __construct()
    {

        $this->wishbrandModel = new WishbrandModel();
        $this->es = new Test();
    }

    public function findById(int $id)
    {

        $data = $this->wishbrandModel->findById($id);

        return $data;
    }
    public function insertEs($data)
    {
        $Esid = null;
        try {
            $Esid = SnowFlakeUtils::generateID();
        }catch (\Exception $e){

        }

        $return = $this->es->insert($data,$Esid);

        return $return;
    }
}