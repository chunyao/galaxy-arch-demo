<?php

class Loader
{
    /* 路径映射 */
    public static $vendorMap = array(
        'Galaxy' => __DIR__ . DIRECTORY_SEPARATOR,
    );

    /**
     * 自动加载器
     */
    public static function autoload($class)
    {

        $file = self::findFile($class);
        if (file_exists($file)) {
            self::includeFile($file);
        }
    }

    /**
     * 解析文件路径
     */
    private static function findFile($class)
    {
        $vendor = substr($class, 0, strpos($class, '\\')); // 顶级命名空间

        if (isset(self::$vendorMap[$vendor])) {
            $vendorDir = self::$vendorMap[$vendor];
        } else {
            $vendorDir = "";
        }


        $filePath = substr($class, strlen($vendor)) . '.php'; // 文件相对路径
        return strtr($vendorDir . $filePath, '\\', DIRECTORY_SEPARATOR); // 文件标准路径
    }

    /**
     * 引入文件
     */
    private static function includeFile($file)
    {
        if (is_file($file)) {
            include $file;
        }
    }
}

spl_autoload_register('Loader::autoload');
include __DIR__ . DIRECTORY_SEPARATOR . '/Core/CoreProcess.php';
include __DIR__ . DIRECTORY_SEPARATOR . '/Core/RabbitMqProcess.php';
include __DIR__ . DIRECTORY_SEPARATOR . '/Core/function.php';
