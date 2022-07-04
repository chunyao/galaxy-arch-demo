<?php

namespace App\Service;

use App\Repository\Cache\MsgCache;
use App\Repository\Model\MsgModel;

class MsgService
{
    private MsgModel $msgModel;
    private MsgCache $msgCache;

    public function __construct()
    {

        $this->msgModel = new MsgModel();
        $this->msgCache = new MsgCache();

    }

    public function findById($id)
    {

        $return = $this->msgCache->findByIdByCache($id);
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
        unset($this->msgCache);
    }
}
