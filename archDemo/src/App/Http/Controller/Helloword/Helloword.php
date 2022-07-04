<?php
declare(strict_types=1);

namespace App\Http\Controller\Helloword;


use Galaxy\Core\BaseController;
use Galaxy\Core\Log;
use App\Service\MsgService;

class Helloword extends BaseController
{
    private MsgService $msgSevice;

    public function __construct()
    {
        $this->msgSevice = new MsgService();
    }

    public function handler()
    {

        $id = rand(1, 239368);
        $data = $this->msgSevice->findById($id);

        $echo_string = datajson("10200", $data, "success", $cache = false);
        return $echo_string;
    }

    public function __destruct()
    {
        unset($this->msgModel);
    }

}



