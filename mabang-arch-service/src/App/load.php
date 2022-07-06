<?php

class AppLoader
{
    /* 路径映射 */
    public static $vendorMap = array(
        'App' => __DIR__ . DIRECTORY_SEPARATOR . '',
        'Controller' => __DIR__ . DIRECTORY_SEPARATOR.'App'. DIRECTORY_SEPARATOR .'Http'. DIRECTORY_SEPARATOR .'Controller',

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
        $vendorDir ="";

        if (isset(self::$vendorMap[$vendor])) {
            $vendorDir = self::$vendorMap[$vendor];
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

spl_autoload_register('AppLoader::autoload');
