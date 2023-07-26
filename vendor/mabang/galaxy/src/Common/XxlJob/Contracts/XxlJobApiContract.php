<?php
/**
 * Created by PhpStorm.
 * User: XUMORAN
 * Date: 2022-03-02
 * Time: 20:28
 */

namespace  Mabang\Galaxy\Common\XxlJob\Contracts;

interface XxlJobApiContract
{

    /**
     *  心跳注册
     *
     * @return boolean
     *
     */
    public function beat(): bool;
    /**
     *  心跳保活
     *
     * @return boolean
     *
     */
    public function XxlJobRegistry(): bool;
    /**
     * 任务调度，忙碌检测
     *
     * @return boolean
     *
     */
    public function XxlJobIdleBeat($params): bool;
    /**
     *  任务触发
     *
     * @return boolean
     *
     */
    public function run($params): bool;
    /**
     *  消费回执
     *
     * @return boolean
     *
     */
    public function callback($params): bool;


    /**
     *  配置刷入缓存
     *
     * @return boolean
     *
     */
    public function FlushTaskScene(): bool;
}
