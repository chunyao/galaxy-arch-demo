<?php

namespace App\Listener;


use Mabang\Galaxy\Core\Log;


use App;

class TestListener
{


    /*该函数固定且必须*/
    /* 公有曰 1 对应 aaaa
    私有云 1 对应 qqqq
     * */
    public static function getQueue()
    {

        return App::$innerConfig['rabbitmq.queue'][0];
    }

    /* handler 为固定函数，return true or false，ack 强依赖 */
    public function handler($msg): bool
    {
        sleep(1);
        Log::info("消息消费 id:" . $msg['messageId']);

        return true;
        /* 整理 接受msseage 消息*/
        /* 方案一 自己处理消息*/
        /*$i=rand(0,2);
        if ($i==1){
            return true;
        }*/

        /*if (!RDS::instance()->set(App::$innerConfig['rabbitmq.queue'][0] . ":" . $this->msg['messageId'], 1, array('nx', 'ex' => 30))) {
            echo "消息重复消费 id:" . $this->msg['messageId'] . "\n";
            // log::info("消息重复消费 id:" . $this->msg['messageId']);

            return true;
        } else {
            echo "start:" . self::getMillisecond() . "\n";
            $result = $this->msgService->saveMsg($this->msg);
            echo "end:" . self::getMillisecond() . "\n";
            //   echo "漏了";

            return true;
        }*/

        /* 方案二转发消息*/
        //   return $this->msgProxy->sendMessage("http://192.168.2.21:11181/api/default/testSwooleRabbitMq", $this->msg);
        //   return true;
    }

    public function __destruct()
    {


    }

    /** * 时间戳 - 精确到毫秒 * @return float */
    public static function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }
}
