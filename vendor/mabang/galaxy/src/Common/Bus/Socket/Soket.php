<?php

namespace Mabang\Galaxy\Common\Bus\Socket;

use Co;
use Swoole;
class Socket
{

    public static  function recieve(){
        $socket = new Co\Socket(AF_UNIX,SOCK_STREAM,0);
        $socket->bind("/tmp/server.sock");
        go(function ()use($socket){
            while(true) {
                echo "Accept: \n";
                $client = $socket->accept();
                if ($client === false) {
                    var_dump($socket->errCode);
                } else {
                    Swoole\Event::add($client,function($client){
                        if(!$client->checkLiveness()){
                            $client->close();
                            Swoole\Event::del($client);
                            return;
                        }
                        echo $client->fd."****".$client->recv().PHP_EOL;
                        $client->send("world");
                    });
                }
            }
        });

    }

    public static  function send($msg){
        $socket = new Co\Socket(AF_UNIX,SOCK_STREAM,0);

        go(function () use ($socket) {
            $retval = $socket->connect("/tmp/server.sock");
            while ($retval)
            {
                $socket->send("hello");
                $data = $socket->recv();
                echo "server recv: ".$data.PHP_EOL;
                if (empty($data)) {
                    $socket->close();
                    break;
                }
                co::sleep(1.0);
            }
        });

    }
}