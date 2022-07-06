<?php

namespace App\Repository\Model;

use App\Config\Suffix;

class WishbrandModel
{
    private string $table;

    public function __construct()
    {
        $this->table = 'db_wish_brand';
    }

    public function findById(int $id)
    {

        $data = Suffix::instance()->tableSuffix($this->table, 100001, 10)->where('id = ?', $id)->first();

        return $data;
    }

    public function __destruct()
    {

    }
}