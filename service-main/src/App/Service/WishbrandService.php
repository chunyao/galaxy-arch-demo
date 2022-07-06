<?php

namespace App\Service;


use App\Config\Suffix;
use App\Repository\Model\WishbrandModel;

class WishbrandService
{
    private WishbrandModel $wishbrandModel;

    public function __construct()
    {

        $this->wishbrandModel = new WishbrandModel();

    }

    public function findById(int $id)
    {

        $data = $this->wishbrandModel->findById($id);

        return $data;
    }
}