<?php

namespace Galaxy\Core;


use Mix\Vega\Abort;
use Mix\Vega\Context;
use Mix\Vega\Engine;
use Mix\Vega\Exception\NotFoundException;
use Galaxy\Core\Log;
class Vega
{

    /**
     * @return Engine
     */
    public static function new($appName): Engine
    {
        $vega = new Engine();

        // 500
        $vega->use(function (Context $ctx) {

            try {
                $ctx->next();
            } catch (\Throwable $ex) {
                if ($ex instanceof Abort || $ex instanceof NotFoundException) {
                    throw $ex;
                }
                //var_dump($ex->getTrace()[0]);
                Log::error(sprintf('%s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
                $ctx->string(500, 'Internal Server Error');
                $ctx->abort();
            }
        });

        // debug
        /*if (APP_DEBUG) {
            $vega->use(function (Context $ctx) {
                $ctx->next();
                Log::debug(sprintf(
                    '%s|%s|%s|%s',
                    $ctx->method(),
                    $ctx->uri(),
                    $ctx->response->getStatusCode(),
                    $ctx->remoteIP()
                ));
            });
        }*/

        // routes
        $routes = require __DIR__ . '/../../'.$appName.'/src/App/Routes/index.php';
        $routes($vega,$appName);

        return $vega;
    }

}
