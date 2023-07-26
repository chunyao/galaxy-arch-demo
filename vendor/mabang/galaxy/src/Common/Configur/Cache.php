<?php

namespace Mabang\Galaxy\Common\Configur;


use Galaxy\Common\Configur\Database;
use Mabang\Galaxy\Common\Utils\LocalCache;

class Cache
{

    /**
     * @var LocalCache
     */
    private static $instance;

    public static function init(): void
    {
        self::$instance = new LocalCache();
    }
    /**
     * @return Database
     */
    public static function instance(): LocalCache
    {
        if (!isset(self::$instance)) {
            self::$instance = new LocalCache();
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
