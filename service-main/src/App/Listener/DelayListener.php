<?php

namespace App\Listener;


use App\Config\RDS;
use App\Service\MsgProxyService;
use Swoole;
use Mabang\Galaxy\Core\Log;
use Mabang\Galaxy\Service\QueueService;
use App\Service\MsgService;

use App;

class DelayListener
{

    private QueueService $queueService;

    public array $msg;

    private MsgService $msgService;

    private MsgProxyService $msgProxy;

    /*该函数固定且必须*/
    /* 公有曰 1 对应 aaaa
    私有云 1 对应 qqqq
     * */
    public static function getQueue()
    {

        return App::$innerConfig['rabbitmq.queue'][1];
    }

    public function __construct($msg)
    {
        /**
         * 公有云 1 对应 aaaa
         * 私有云 1 对应 qqqq
         **/
        //    $this->queueService = new QueueService();
        //      $this->msgService = new MsgService();
        //       $this->msgProxy = new MsgProxyService();
        //  var_dump($msg);
        //    sleep(1);
        $this->msg = $msg;

    }

    /* handler 为固定函数，return true or false，ack 强依赖 */
    public function handler(): bool
    {
       // sleep(1);
        // echo json_encode($this->msg);
        return true;
        /* 整理 接受msseage 消息*/
        /* 方案一 自己处理消息*/

        // if (!RDS::instance()->set(App::$innerConfig['rabbitmq.queue'][0] . ":" . $this->msg['id'],1, array('nx', 'ex' => 30))) {
        //echo "消息重复消费 id:". $this->msg['id']."\n";
        //   log::info("消息重复消费 id:". $this->msg['id']);
        //

        // }else{
        //     echo "start:" . self::getMillisecond()."\n";
        //   $result = $this->msgService->saveMsg($this->msg);
        //       echo "end:" . self::getMillisecond()."\n";
        //   echo "漏了";

        //    return true;
        // }

        /* 方案二转发消息*/
        //   return $this->msgProxy->sendMessage("http://192.168.2.21:11181/api/default/testSwooleRabbitMq", $this->msg);
        //   return true;
    }

    public function __destruct()
    {
        //    unset($this->msgService);
        //  unset($this->QueueService);
        //  unset($this->msgProxy);

    }

    /** * 时间戳 - 精确到毫秒 * @return float */
    public static function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }
}
