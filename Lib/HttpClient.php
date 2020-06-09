<?php

declare(strict_types=1);

namespace PinboardPHP\Lib;

use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    protected $http_client;

    public function __construct($http_client = null)
    {
        $this->http_client = $http_client ?? (new \GuzzleHttp\Client());
    }

    public function request($method, $path, $query_options): ResponseInterface
    {
        return $this->http_client->request($method, $path,  ['query' => $query_options]);
    }
}