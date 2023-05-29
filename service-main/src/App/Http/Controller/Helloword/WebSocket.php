<?php

namespace App\Http\Controller\Helloword;


use Mabang\Galaxy\Common\Configur\Upgrader;
use Mabang\Galaxy\Core\Session;
use Mix\Vega\Context;



class WebSocket
{
    /**
     * @param Context $ctx
     */
    public function index(Context $ctx)
    {

        $conn = Upgrader::instance()->upgrade($ctx->request, $ctx->response);

        $session = new Session($conn);
        $session->start('WebScoketDemoHandler');
    }
}