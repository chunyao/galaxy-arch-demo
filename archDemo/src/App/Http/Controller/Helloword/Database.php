<?php

namespace App\Http\Controller\Helloword;

use App\Config\DB;
use Galaxy\Core\Log;
use Mix\Vega\Context;


class Database
{
    public function databasetest(Context $ctx)
    {
        /* 数据库插入*/
        //     $lastId  = DB::instance()->insert('user', ['name' => 'foo','age' => 20,])->lastInsertId();;
        //    log::debug("获取lastId: {$lastId}");

        /*查询*/
        //     $data =  json_encode(DB::instance()->table('user')->where('id = ?', 1)->first());
        //    log::debug("获取id为1 数据: {$data}");

        /*更新*/
        $return = DB::instance()->table('user')->where('id = ?', 1)->update('age', '1')->rowCount();
        var_dump($return);

        $return = DB::instance()->table('user')->where('id = ?', 1)->order('id', 'desc')->offset(0)->limit(5)->get();

        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $return
        ]);

    }

}