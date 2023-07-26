<?php

use Mabang\Galaxy\Common\XxlJob\Controller\XxlJobController;

return function (Mix\Vega\Engine $vega) {
    $vega->handle('/beat',null,null, [new XxlJobController(), 'beat'])->methods('POST');
    $vega->handle('/idleBeat', null,null,[new XxlJobController(), 'idleBeat'])->methods('POST');
    $vega->handle('/run',null,null, [new XxlJobController(), 'run'])->methods('POST');
};