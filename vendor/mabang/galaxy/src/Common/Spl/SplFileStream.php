<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/22
 * Time: 下午2:55
 */

namespace Mabang\Galaxy\Common\Spl;


use Mabang\Galaxy\Common\Spl\SplStream;

class SplFileStream extends SplStream
{
    function __construct($file,$mode = 'c+')
    {
        $fp = fopen($file,$mode);
        parent::__construct($fp);
    }

    function lock($mode = LOCK_EX){
        return flock($this->getStreamResource(),$mode);
    }

    function unlock($mode = LOCK_UN){
        return flock($this->getStreamResource(),$mode);
    }
}