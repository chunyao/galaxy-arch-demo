<?php
/**
 * Created by PhpStorm.
 * User: XUMORAN
 * Date: 2022-03-02
 * Time: 10:15
 */

namespace MabangSdk\XxlJob\Models;
//todo 考虑下是否优化掉
use Mabang\Helpers\Db\Model;

class TaskXxljobSceneModel extends Model
{
    // -----------------------------------------------------------------------------------------------------------------
    // 属性定义
    // -----------------------------------------------------------------------------------------------------------------
    protected $connection = 'v2-mabang-rabbitmq';
    protected $table = 'task_xxljob_scene';
 }
