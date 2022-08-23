<?php

use Galaxy\Common\XxlJob\Controller\XxlJobController;

return function (Mix\Vega\Engine $vega) {
    $vega->handle('/beat', [new XxlJobController(), 'beat'])->methods('POST');
    $vega->handle('/idleBeat', [new XxlJobController(), 'idleBeat'])->methods('POST');
    $vega->handle('/run', [new XxlJobController(), 'run'])->methods('POST');
};