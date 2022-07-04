<?php

namespace App\Service;

use App\Repository\Model\MsgModel;

class MsgService
{
    private MsgModel $msgModel;

    public function __construct()
    {

        $this->msgModel = new MsgModel();

    }

    public function findById($id)
    {

        $return = $this->msgModel->findById($id);
        return $return;
    }

    public function saveMsg($msg)
    {

        $return = $this->msgModel->insertMsg($msg);

        return $return;
    }

    public function __destruct()
    {
        unset($this->msgModel);
    }
}
