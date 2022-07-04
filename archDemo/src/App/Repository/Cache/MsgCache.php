<?php

namespace App\Repository\Cache;

use App\Repository\Model\MsgModel;

class MsgCache
{
    private MsgModel $msgModel;

    public function __construct()
    {
        $this->msgModel = new MsgModel();
    }

    public function findByIdByCache(int $id)
    {

        $data =  $this->msgModel->findById($id);

        return $data;
    }

}