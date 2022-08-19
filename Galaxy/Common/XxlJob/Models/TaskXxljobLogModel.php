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

class TaskXxljobLogModel extends Model
{
    // -----------------------------------------------------------------------------------------------------------------
    // 属性定义
    // -----------------------------------------------------------------------------------------------------------------
    protected $connection = 'v2-mabang-rabbitmq';
    protected $table = 'task_xxljob_log';


    const TASK_TYPE_SYNC = 1; //同步
    const TASK_TYPE_ASYN = 2; //异步


    const TASK_STATUS_NOT_ACK    = 0; //未应答
    const TASK_STATUS_IN_PROCESS = 1; // 处理中
    const TASK_STATUS_ACKED      = 2; // 已经应答
    const TASK_STATUS_FAIL       = 3; //处理失败

    public static function getOne($logId)
    {
        return self::query()->where('log_id', $logId)->first();
    }
 }
