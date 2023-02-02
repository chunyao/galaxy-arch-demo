<?php

namespace App\Http\Controller\Helloword;

use Galaxy\Common\Configur\Upgrader;
use Galaxy\Core\Session;


class WebSocket
{
    /**
     * @param Context $ctx
     */
    public function index(Context $ctx)
    {
        $conn = Upgrader::instance()->upgrade($ctx->request, $ctx->response);
        $session = new Session($conn);
        $session->start();
    }
}