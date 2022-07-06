<?php

namespace App\Service;
use pheign\annotation\method\GET;
use pheign\annotation\Options;
use pheign\annotation\Pheign;
use pheign\annotation\Target;
class SayFeignService
{
    /**
     * @Pheign
     *
     * @GET
     * @Target("/mabang-arch-demo/helloword/helloword")
     *
     * @Options(CURLOPT_SSL_VERIFYHOST=0, CURLOPT_SSL_VERIFYPEER=0)
     */
    public function gethello(){}

    /**
     * @Pheign
     *
     * @GET
     * @Target("/repos/{owner}/{repo}")
     *
     * @Options(CURLOPT_SSL_VERIFYHOST=0, CURLOPT_SSL_VERIFYPEER=0)
     */
    public function repositoryInformations($owner, $repo){
        var_dump($owner);
        var_dump($repo);
    }

    /**
     * @Pheign
     *
     * @POST
     * @Target("/repos/post")
     *
     * @Options(CURLOPT_SSL_VERIFYHOST=0, CURLOPT_SSL_VERIFYPEER=0)
     */
    public function helloPost($data){
        var_dump($data);

    }
}