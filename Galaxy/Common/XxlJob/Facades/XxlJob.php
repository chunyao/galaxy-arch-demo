<?php
/**
 * Created by PhpStorm.
 * User: sky
 * Date: 2020-06-03
 * Time: 08:11
 */

namespace MabangSdk\XxlJob\Facades;

use MabangSdk\XxlJob\Support\XxlJob\XxlJobApi;
use MabangSdk\XxlJob\Support\XxlJob\Contracts\XxlJobApiContract;

use Illuminate\Support\Facades\Facade;

/**
 * Class Dict
 * @package MabangSdk\XxlJob\Facades
 *
 * @method static boolean beat(array $params)
 * @method static boolean XxlJobRegistry()
 * @method static boolean XxlJobIdleBeat(array $params)
 * @method static boolean run(array $params)
 * @method static boolean callback(array $params)
 * @method static boolean FlushTaskScene()
 *
 */

class XxlJob extends Facade
{
    protected static $isRegister = false;

    public static function getContract(): string
    {
        return XxlJobApiContract::class;
    }

    public static function getFacadeAccessor(): string
    {
        if (!static::$isRegister) {
            static::$app->singleton(XxlJobApiContract::class, XxlJobApi::class);
            static::$isRegister = true;
        }

        return static::getContract();
    }
}
