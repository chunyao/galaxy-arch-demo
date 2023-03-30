<?php

namespace App\Handler;

class WebScoketDemoHandler
{
    private $session;

    public function __construct($session)
    {
        $this->session = $session;
    }

    public function handler($req)
    {

        swoole_timer_tick(5000, function () use ($req) {
            try {
                if ($req === 1) {
                    $this->session->send("test");

                }
                $this->session->send("heatbeat");
            } catch (\Throwable $e) {
                //var_dump($e);
            }
        });
    }

}