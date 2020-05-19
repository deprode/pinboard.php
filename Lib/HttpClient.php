<?php


namespace PinboardPHP\Lib;


class HttpClient
{
    protected $http_client;

    public function __construct($http_client = null)
    {
        $this->http_client = $http_client ?? (new \GuzzleHttp\Client());
    }

    public function request($method, $path, $query_options)
    {
        return $this->http_client->request($method, $path,  ['query' => $query_options]);
    }
}