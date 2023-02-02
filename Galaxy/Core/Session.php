<?php

namespace Galaxy\Core;

use Mix\WebSocket\Connection;
use Swoole\Coroutine\Channel;


class Session
{
    /**
     * @var Connection
     */
    protected $conn;
    public static $handlerClasses;
    /**
     * @var Channel
     */
    protected $writeChan;

    /**
     * Session constructor.
     * @param Connection $conn
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
        $this->writeChan = new Channel(10);
    }

    /**
     * @param string $data
     */
    public function send(string $data): void
    {
        $this->writeChan->push($data);
    }

    public function start($class): void
    {

        // 接收消息
        go(function () use($class) {
            while (true) {
                $frame = $this->conn->readMessage(-1);
                $message = $frame->data;

                $pre= "App\Handler\\";
                try{
                    $handler =  $pre.$class;
                    (new $handler($this))->handler($message);
                }catch (\Throwable $e){
                    var_dump($e->getMessage());
                }


            }
        });

        // 发送消息
        go(function () {
            while (true) {
                $data = $this->writeChan->pop();
                if (!$data) {
                    return;
                }
                $frame = new \Swoole\WebSocket\Frame();
                $frame->data = $data;
                $frame->opcode = WEBSOCKET_OPCODE_TEXT; // or WEBSOCKET_OPCODE_BINARY
                $this->conn->writeMessage($frame);
            }
        });
    }


}