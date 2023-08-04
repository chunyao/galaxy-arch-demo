<?php

namespace Mabang\Galaxy\Common\HttpClient;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface ConnectionInterface
 * @method ResponseInterface get($uri, array $options = [])
 * @method ResponseInterface request(string $method, $uri, array $options = [])
 * @method ResponseInterface send(RequestInterface $request, array $options = [])
 */
interface ConnectionInterface
{
}
