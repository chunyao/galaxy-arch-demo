<?php

namespace Mabang\Galaxy\Common\HttpClient;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface ConnectionInterface
 * @method ResponseInterface request(string $method, $uri, array $options = [])
 * @method ResponseInterface send(RequestInterface $request, array $options = [])
 * @method ResponseInterface close()
 */
interface ConnectionInterface
{
}
