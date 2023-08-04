<?php

namespace Mabang\Galaxy\Common\HttpClient\Pool;

use GuzzleHttp\Client;
use Mix\ObjectPool\DialerInterface;
use Mabang\Galaxy\Common\HttpClient\Driver;

/**
 * Class Dialer
 * @package Mabang\Galaxy\Common\HttpClient
 */
class Dialer implements DialerInterface
{


    public function __construct()
    {
    }

    /**
     * @return Driver
     */
    public function dial(): object
    {
        return new Driver();;
    }

}
