<?php

namespace App\Http\Controller\Helloword;

use App\Config\DB;
use App\Config\RDS;
use Galaxy\Core\Log;
use Mix\Vega\Context;


class Database
{
    public function databasetest(Context $ctx)
    {
        /* 数据库插入*/
  //      $lastId = DB::instance()->insert('user', ['name' => 'foo', 'age' => 20,])->lastInsertId();;
  //      log::debug("获取lastId: {$lastId}");

        /*查询*/
  //      $data = json_encode(DB::instance()->table('user')->where('id = ?', 1)->first());
  //      log::debug("获取id为1 数据: {$data}");

        /*更新*/
  //      $return = DB::instance()->table('user')->where('id = ?', 1)->update('age', '1')->rowCount();
  //      var_dump($return);

        /*删除*/
  //      $return =  DB::instance()->table('user')->where('id = ?', 1)->delete()->rowCount();
  //      var_dump($return);

        /*替换创建*/
 /*       $data = [
            'name' => 'foo',
            'age' => 0,
        ];
        $return =  DB::instance()->insert('user', $data, 'REPLACE INTO')->lastInsertId();
        var_dump($return);*/

        /*批量创建*/
        /*$data = [
            [
                'name' => 'foo3',
                'age' => 1,
            ],
            [
                'name' => 'foo1',
                'age' => 2,
            ]
        ];
        $return =  DB::instance()->batchInsert('user', $data)->rowCount();
        var_dump($return);*/
        $tx = DB::instance()->beginTransaction();
        try {
            $data = [
                'name' => 'foo',
                'age' => 99,
            ];
            $tx->insert('user', $data);
            $tx->commit();
        } catch (\Throwable $ex) {
            $tx->rollback();
            throw $ex;
        }

        /*使用函数创建*/
        $data = [
            'name' => 'fdsoo',
            'age' => 0,
            'ctime' => new \Mix\Database\Expr('CURRENT_TIMESTAMP()'),
        ];
        $return =  DB::instance()->insert('user', $data)->lastInsertId();
        var_dump($return);

        DB::instance()->debug(function (\Mix\Database\ConnectionInterface $conn) {
            var_dump($conn->queryLog()); // array, fields: time, sql, bindings
        })
            ->table('user')
            ->where('id = ?', 1)
            ->get();
        $return = DB::instance()->table('user')->where('id = ?', 1)->order('id', 'desc')->offset(0)->limit(5)->get();

        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $return
        ]);

    }

    public function redistest(Context $ctx){
        $tx =  RDS::instance()->multi();
        $tx->set('foo', 'bar');
        $tx->set('foo1', 'bar1');
        $ret = $tx->exec();
        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => RDS::instance()->get("foo")
        ]);
    }

}