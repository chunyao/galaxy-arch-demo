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
        if ($req == 1) {
            $this->session->send("test");
            return;
        }
      $this->session->send("sdf");
    }

}