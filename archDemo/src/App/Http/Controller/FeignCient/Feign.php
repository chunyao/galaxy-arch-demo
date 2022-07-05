<?php

use \Mix\Vega\Context;

class Feign{
    public function fegin1(Context $ctx)
    {
        // Initialisation de pheign
        $pheign = \pheign\builder\Pheign::builder()->target(\App\Service\SayFeignService::class, 'http://127.0.0.1:8080');

        $result = $pheign->repositories('airmanbzh');

        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $result
        ]);

    }

    public function fegin2(Context $ctx)
    {
        // Initialisation de pheign
        $pheign = \pheign\builder\Pheign::builder()->target(\App\Service\SayFeignService::class, 'http://127.0.0.1:8080');

        $result = $pheign->helloPost('airmanbzh');

        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $result
        ]);

    }
    public function fegin3(Context $ctx)
    {
        // Initialisation de pheign
        $pheign = \pheign\builder\Pheign::builder()->target(\App\Service\SayFeignService::class, 'http://127.0.0.1:8080');

        $result = $pheign->repositoryInformations('airmanbzh','abcd');

        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $result
        ]);

    }
}