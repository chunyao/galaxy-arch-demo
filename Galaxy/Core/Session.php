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

    public function start(): void
    {
        // 接收消息
        go(function () {
            while (true) {
                $frame = $this->conn->recv();
                $message = $frame->data;
                var_dump($message);
              //  (new Hello($this))->index($message);
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
                $this->conn->send($frame);
            }
        });
    }
}