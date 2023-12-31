<?php

namespace App\Repository\Model;

use App\Config\DB;
use \App\Config\Stock;

class MsgModel
{
    private string $table;

    public function __construct()
    {
        $this->table = 'msg';
    }

    public function findById(int $id)
    {

        $data =  DB::instance()->table( $this->table )->where('id = ?', $id)->first();

        return $data;
    }

    public function insertMsg(array $msg):int
    {

        $id = DB::instance()->batchInsert( DB::instance()->getTableSuffix( $this->table,100,10),$msg)->lastInsertId();
        return $id;
    }


    public function __destruct()
    {

    }
}