<?php

namespace App\Service;

class SayFeignService
{
    /**
     * @Pheign
     *
     * @GET
     * @Target("/users/{owner}/repos")
     *
     * @Options(CURLOPT_SSL_VERIFYHOST=0, CURLOPT_SSL_VERIFYPEER=0)
     */
    public function repositories($owner){

        var_dump($owner);
    }

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