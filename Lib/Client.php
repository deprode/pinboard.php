<?php


namespace PinboardPHP\Lib;


class Client
{
    private $client;
    private $token;
    private $baseurl;
    private $default_option;
    private $validate;

    public function __construct(string $token = '', $client = null)
    {
        $this->client = new HttpClient($client);
        $this->baseurl = 'https://api.pinboard.in/v1/';

        $this->validate = new OptionValidation();

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

    public function recentPosts($option = [])
    {
        if ($this->validate->validate($option, ['tag' => 'tag', 'count' => 'integer'])){
            throw new \Exception('オプションエラー');
        }

        $response = $this->request('GET', 'posts/recent', $option);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('エラー');
        }

        return $response;
    }

    public function datesPosts($option = [])
    {
        if ($this->validate->validate($option, ['tag' => 'tag'])){
            throw new \Exception('オプションエラー');
        }

        $response = $this->request('GET', 'posts/dates', $option);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('エラー');
        }

        return $response;
    }

    public function userSecret()
    {
        $response = $this->request('GET', 'user/secret');

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('エラー');
        }

        return $response;
    }

    public function userToken()
    {
        $response = $this->request('GET', 'user/api_token');

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('エラー');
        }

        return $response;
    }

    public function notesList()
    {
        $response = $this->request('GET', 'notes/list');

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('エラー');
        }

        return $response;
    }

    public function noteById($id)
    {
        if (ctype_xdigit($id) === false){
            throw new \Exception('オプションエラー');
        }
        $response = $this->request('GET', 'notes/'.$id);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('エラー');
        }

        return $response;
    }
}