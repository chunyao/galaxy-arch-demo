<?php
/**
 * Created by PhpStorm.
 * User: XUMORAN
 * Date: 2022-03-02
 * Time: 20:28
 */

namespace Galaxy\Common\XxlJob;

use  Galaxy\Common\XxlJob\Contracts\XxlJobApiContract;

class XxlJobApi implements XxlJobApiContract
{
    public function beat(): bool
    {
        return XxlJobService::XxlJobBeat();
    }

    public function XxlJobRegistry(): bool
    {
        return XxlJobService::XxlJobRegistry();
    }

    public function XxlJobIdleBeat($params): bool
    {
        return XxlJobService::XxlJobIdleBeat($params);
    }


    public function run($params): bool
    {
        return (new XxlJobService())->XxlRun($params);
    }

    public function callback($params): bool
    {
        return XxlJobService::XxlJobCallback($params);
    }

    /**
     *  配置刷入缓存
     *
     * @return boolean
     *
     */
    public function FlushTaskScene(): bool
    {
        return (new XxlJobService())->setSceneCache();
    }
}
