<?php
namespace App\Http\Vo\LocalCache;
use Mabang\Galaxy\Http\Bo\BaseBean;

class ReqVo extends BaseBean
{
    public string $key;
    public string $val;
}