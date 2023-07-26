<?php

namespace Mabang\Galaxy\Common\Configur;


use Galaxy\Common\Configur\Database;
use Mabang\Galaxy\Common\Utils\SnowFlakeUtils;
use Mabang\Galaxy\Core\Once;

class SnowFlake
{
    private static $instance;
    private static $dataCenterId;
    private static $bizId;
    private static $once;
    public static function init(): void
    {
        self::$once = new Once();
        self::$dataCenterId = SnowFlakeUtils::getDataCenterId();
        self::$bizId = SnowFlakeUtils::getBizId("OtherId");

    }

    /**
     * @return SnowFlakeUtils
     */
    public static function instance(): SnowFlakeUtils
    {
        if (!isset(self::$instance)) {
            static::$once->do(function () {
                self::$instance = new SnowFlakeUtils(self::$dataCenterId, self::$bizId);
            });
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