<?php

namespace App\Service;
use  \Php\Micro\Grpc\Greeter;
class SayService implements \Php\Micro\Grpc\Greeter\SayInterface
{
    public function Hello(\Mix\Grpc\Context $context, \Php\Micro\Grpc\Greeter\Request $request): \Php\Micro\Grpc\Greeter\Response
    {
        $response = new \Php\Micro\Grpc\Greeter\Response();
        $response->setMsg(sprintf('hello, %s', $request->getName()));
        return $response;
    }

}