<?php


namespace PinboardPHP\Lib;


class Client
{
    private $client;
    private $token;
    private $baseurl;
    private $default_option;

    public function __construct(string $token = '', $client = null)
    {
        $this->client = new HttpClient($client);
        $this->baseurl = 'https://api.pinboard.in/v1/';

        $this->token = $token;
        $this->default_option = ['auth_token' => $this->token, 'format' => 'json'];
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
        $this->default_option['auth_token'] = $this->token;
    }

    private function request(string $method, string $path, array $user_option = [])
    {
        $uri = $this->baseurl . $path;
        $option = $user_option + $this->default_option;
        return $this->client->request($method, $uri, $option);
    }

    public function lastUpdatePosts()
    {
        $response = $this->request('GET', 'posts/update');

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('エラー');
        }

        return $response;
    }
}