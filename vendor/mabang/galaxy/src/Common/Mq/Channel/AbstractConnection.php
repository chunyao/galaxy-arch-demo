<?php

namespace Mabang\Galaxy\Common\Mq\Channel;

use Mabang\Galaxy\Common\Spl\Exception\Exception;
use Mabang\Galaxy\Core\Log;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;


/**
 * Class AbstractConnection
 * @package Mix\Database
 */
abstract class AbstractConnection implements ConnectionInterface
{

    /**
     * 驱动
     * @var Driver
     */
    protected $driver;

    private $ack = false;
    /**
     * @var LoggerInterface
     */
    protected $logger;


    /**
     * values
     * @var array
     */
    protected $values = [];


    /**
     * AbstractConnection constructor.
     * @param Driver $driver
     * @param LoggerInterface|null $logger
     */
    public function __construct(Driver $driver)
    {
        $this->driver = $driver;

    }

    /**
     * 返回当前rabbitt连接是否在事务内（在事务内的连接回池会造成下次开启事务产生错误）
     * @return bool
     */
    public function inTransaction(): bool
    {
        try {
            return $this->ack;
        } catch (\Throwable $e) {
            Log::error(sprintf('inTransaction %s in %s on line %d', $e->getMessage(), $e->getFile(), $e->getLine()));
            return false;
        }
    }

    /**
     * 连接
     * @throws \Exception
     */
    public function connect(): void
    {
        $this->driver->connect();
    }

    /**
     * 关闭连接
     */
    public function close(): void
    {

        $this->driver->close();
    }

    /**
     * 重新连接
     * @throws \Exception
     */
    protected function reconnect(): void
    {
        $this->close();
        $this->connect();
    }

    /**
     * 判断是否为断开连接异常
     * @param \Throwable $ex
     * @return bool
     */
    protected static function isDisconnectException(\Throwable $ex)
    {
        $disconnectMessages = [
            'fwrite', 'Call to undefined method', 'Missed server heartbeat', 'Undefined', 'CHANNEL_ERROR', 'Channel'
        ];
        $errorMessage = $ex->getMessage();
        foreach ($disconnectMessages as $message) {
            if (false !== stripos($errorMessage, $message)) {
                return true;
            }
        }
        return false;
    }

    public function obj():AMQPChannel
    {
        return $this->driver->instance();
    }

    /**
     * @param $messageBody  消息内容
     * @param $exchange     交换机名
     * @param $routeKey     路由键
     * @param array $head
     * @return bool
     */
    private function setAckHandler(){

    }
    public function publish($messageBody, $exchange, $routeKey, $head = [], $ack = 0, $retry = 0): int
    {

        $status = 0;
        try {
            $head = array_merge(array('content_type' => 'text/json', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT), $head);
            $message = new AMQPMessage($messageBody, $head);
            //推送成功
            if ($ack === 1) {
                $this->ack = true;
                $this->driver->instance()->set_ack_handler(
                    function (AMQPMessage $message) use (&$status) {
                        $status = 1;
                    //    echo "发送成功: " . $message->body . PHP_EOL;
                    }
                );

                //推送失败
                $this->driver->instance()->set_nack_handler(
                    function (AMQPMessage $message) use (&$status) {
                        $status = 2;
                    //    echo "发送失败: " . $message->body . PHP_EOL;
                    }
                );

            $this->driver->instance()->set_return_listener(
                function ($replyCode, $replyText, $exchange, $routingKey, AMQPMessage $message) {
                  //  echo 'Message returned with content ' . $message->body . PHP_EOL;
                }
            );
                $this->driver->instance()->confirm_select();
            }
            $this->driver->instance()->basic_publish($message, $exchange, $routeKey,true);

            if ($ack === 1) {
                $this->driver->instance()->wait_for_pending_acks_returns();

             //  $this->driver->close();
                $this->driver->reconnect();
            }
            $this->ack = false;
            //    unset($message);
        } catch (\Throwable $ex) {
            Log::error(sprintf('message publish: %s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
            throw new Exception($ex);
        }
        // 响应ack
        //  unset($messageBody);
       //
        return $status;
    }

}
