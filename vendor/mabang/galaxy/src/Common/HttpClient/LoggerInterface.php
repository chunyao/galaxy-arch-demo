<?php

namespace Mabang\Galaxy\Common\HttpClient;

/**
 * Interface LoggerInterface
 * @package Mabang\Galaxy\Common\HttpClient
 */
interface LoggerInterface
{

    public function trace(float $time, string $cmd, array $args, ?\Throwable $exception): void;

}