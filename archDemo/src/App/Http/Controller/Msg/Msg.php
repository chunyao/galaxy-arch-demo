<?php

namespace App\Http\Controller;

use Galaxy\Common\Mysql\Db;
use Galaxy\Core\BaseController;
use Galaxy\Core\Log;
use App\Service\MsgService;
class Msg
{

    private  $msgService;

    public function __construct() {

        $this->msgService = new MsgService();

    }
    public function handler()
    {
        $msgService = new MsgService();

        $id = rand(1, 1695705);

        $data = $this->msgService->findById($id);
        $echo_string = datajson("10200", $data, "success", $cache = false);
        return $echo_string;
    }
}