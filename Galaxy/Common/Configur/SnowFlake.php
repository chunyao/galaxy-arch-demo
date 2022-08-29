<?php

namespace Galaxy\Common\Configur;



use Galaxy\Common\Utils\SnowFlakeUtils;

class SnowFlake
{
    private static $instance;
    private static $dataCenterId;
    private static $bizId;
    public static function init(): void
    {
        self::$dataCenterId = SnowFlakeUtils::getDataCenterId();
        self::$bizId = SnowFlakeUtils::getBizId("OtherId");

    }
    /**
     * @return Database
     */
    public static function instance(): SnowFlakeUtils
    {
        if (!isset(self::$instance)) {
            self::$instance = new SnowFlakeUtils(self::$dataCenterId,  self::$bizId);
        }
        return self::$instance;
    }

    /**
     * @return void
     */
    public static function enableCoroutine(): void
    {

    }
}