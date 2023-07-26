<?php

namespace Mabang\Galaxy\Core;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Mabang\Galaxy\Common\Annotation\Route;
use App\Http\Controller\LocalCache\LocalCacheController;
use Mabang\Galaxy\Common\Configur\TraceRecord;
use Mabang\Galaxy\Common\Spl\Exception\Exception;
use Mabang\Galaxy\Core\Log;
use Mix\Vega\Abort;
use Mix\Vega\Context;
use Mix\Vega\Engine;
use Mix\Vega\Exception\NotFoundException;


class Vega
{
    private static $routeClasses;

    static function getDir($path)
    {
        //判断目录是否为空
        if (!file_exists($path)) {
            return [];
        }

        $files = scandir($path);
        $fileItem = [];
        foreach ($files as $v) {
            $newPath = $path . DIRECTORY_SEPARATOR . $v;
            if (is_dir($newPath) && $v != '.' && $v != '..') {
                $fileItem = array_merge($fileItem, self::getDir($newPath));
            } else if (is_file($newPath)) {
                $fileItem[] = $newPath;
            }
        }

        return $fileItem;
    }

    public static function routeload($appName)
    {

        $dir = ROOT_PATH .
            DIRECTORY_SEPARATOR . "service-main" . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "App" .
            DIRECTORY_SEPARATOR . "Http" . DIRECTORY_SEPARATOR . "Controller" . DIRECTORY_SEPARATOR;
        $i = 0;
        foreach (self::getDir($dir) as $item) {

            $class = str_replace(".php", "", str_replace("/", "\\", explode("//", $item)[1]));

            self::$routeClasses[$i] = '\App\Http\Controller\\' . $class;
            $i++;
        }

        return self::$routeClasses;
    }

    /**
     * @return Engine
     */
    public static function new($appName): Engine
    {
        self::routeload($appName);
        AnnotationRegistry::registerLoader('class_exists');
        $vega = new Engine();

        // 500
        $vega->use(function (Context $ctx) {

            try {
                $ctx->next();
            } catch (\Throwable $ex) {
                if ($ex instanceof Abort || $ex instanceof NotFoundException) {
                    throw $ex;
                }
                Log::error(sprintf('%s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
                $ctx->string(500, 'Galaxy Internal Server Error');
                $ctx->abort();
            }
        });

        // routes

        self::initRoute($vega, $appName);

        return $vega;
    }

    private static function initRoute($vega, $appName)
    {

        $sub = $vega->pathPrefix("/" . $appName);
        //全局切面， 类似lavarel 中间件
        $sub->use(function (\Mix\Vega\Context $ctx) {
            TraceRecord::instance()->before($ctx);
            $ctx->next();
            TraceRecord::instance()->after($ctx);
        });
        foreach (self::$routeClasses as $routeItem) {

            $reflectionClass = new \ReflectionClass($routeItem);
            $methods = $reflectionClass->getMethods();

            foreach ($methods as $method) {

                $reader    = new AnnotationReader();
                // 读取Route的注解
                $routeAnnotation = $reader->getMethodAnnotation($method, Route::class);
                try {
                    if (isset($routeAnnotation->route) && isset($routeAnnotation->method)) {
                        echo $method->class." ";
                        echo " 初始化 Api: " . $routeAnnotation->route . " method: " . $routeAnnotation->method . PHP_EOL;
                        $container = new Container(); ;
                        $controller = $container->get($method->class);
                        $sub->handle($routeAnnotation->route, $routeAnnotation->contextType, $routeAnnotation->param, [$controller, $method->name])->methods($routeAnnotation->method);
                    }
                } catch (\Throwable $ex) {
                    var_dump($ex);
                    throw $ex;
                }


            }
            unset($reflectionClass);
        }

    }
}