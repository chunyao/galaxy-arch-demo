<?php

namespace App\Repository\Model\ES;

use App\Config\ES;
class Test
{
    public function insert($data,$esId){

        return ES::instance()->insertDocument($data,$esId);
    }
}