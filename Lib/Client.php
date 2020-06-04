<?php


namespace PinboardPHP\Lib;

use PinboardPHP\Lib\Exception\BadRespnoseException;
use PinboardPHP\Lib\Exception\AuthException;
use PinboardPHP\Lib\Exception\ManyRequestException;
use PinboardPHP\Lib\Exception\OptionException;
use GuzzleHttp\Exception\RequestException;

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

        $response = null;
        try {
            $response = $this->client->request($method, $uri, $option);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            if ($this->isAuthError($response)) {
                throw new AuthException('Auth error');
            }
            elseif ($this->isManyRequestError($response)) {
                throw new ManyRequestException('Too many request');
            }
            elseif ($this->isBadResponse($response)) {
                throw new BadRespnoseException('Error: HTTP status is '.$response->getStatusCode());
            }
            else {
                throw new \Exception($response);
            }
        } catch (\Exception $e) {
            throw new $e;
        }
        return $response;
    }

    protected function isAuthError($response)
    {
        return $response && $response->getStatusCode() === 401;
    }

    protected function isManyRequestError($response)
    {
        return $response && $response->getStatusCode() === 429;
    }

    protected function isBadResponse($response)
    {
        return is_null($response) || ($response && $response->getStatusCode() !== 200);
    }

    public function lastUpdatePosts()
    {
        $response = $this->request('GET', 'posts/update');

        return $response;
    }

    public function recentPosts($option = [])
    {
        if ($this->validate->validate($option, ['tag' => 'tag', 'count' => 'integer'])){
            throw new OptionException('オプションエラー');
        }

        return $this->request('GET', 'posts/recent', $option);
    }

    public function datesPosts($option = [])
    {
        if ($this->validate->validate($option, ['tag' => 'tag'])){
            throw new OptionException('オプションエラー');
        }

        return $this->request('GET', 'posts/dates', $option);
    }

    public function addPost($url, $description, $options)
    {
        // posts/add
        $option = array_filter([
            'url' => $url,
            'description' => $description,
            'extended' => $options['extended'] ?? $options['notes'] ?? '',
            'tags' => $options['tags'] ?? $options['tag'] ?? '',
            'dt' => $options['datetime'] ?? '',
            'replace' => $options['replace'] ?? '',
            'shared' => $options['shared'] ?? '',
            'toread' => $options['toread'] ?? ''
        ]);
        $types = [
            'url' => 'url',
            'description' => 'title',
            'extended' => 'text',
            'tags' => 'tag',
            'dt' => 'datetime',
            'replace' => 'yes',
            'shared' => 'yes',
            'toread' => 'no',
        ];
        if ($this->validate->validate($option, $types)){
            throw new OptionException('オプションエラー');
        }

        return $this->request('GET', 'posts/add', $option);
    }

    public function deletePost($url)
    {
        $option = ['url' => $url];
        if ($this->validate->validate($option, ['url' => 'url'])){
            throw new OptionException('オプションエラー');
        }

        return $this->request('GET', 'posts/delete', $option);
    }

    public function getPost($options)
    {
        // posts/get
        $option = array_filter([
            'tag' => $options['tag'] ?? '',
            'datetime' => $options['datetime'] ?? '',
            'url' => $options['url'] ?? '',
            'meta' => $options['meta'] ?? '',
        ]);
        $types = ['tag' => 'tag', 'dt' => 'datetime', 'url' => 'url', 'meta' => 'no'];
        if ($this->validate->validate($option, $types)){
            throw new OptionException('オプションエラー');
        }

        return $this->request('GET', 'posts/get', []);
    }

    public function allPosts($options = [])
    {
        $fromdt = isset($options['fromdt']) ? (date_create($options['fromdt']))->format('Y-m-d\TH:i:s\Z') : '';
        $todt = isset($options['todt']) ? (date_create($options['todt']))->format('Y-m-d\TH:i:s\Z') : '';
        $option = array_filter([
            'tag' => $options['tag'] ?? '',
            'start' => $options['start'] ?? $options['offset'] ?? 0,
            'results' => $options['results'] ?? $options['count'] ?? null,
            'fromdt' => $fromdt,
            'todt' => $todt,
            'meta' => $options['meta'] ?? ''
        ]);

        $types = [
            'tag' => 'tag',
            'start' => 'int',
            'results' => 'int',
            'fromdt' => 'datetime',
            'todt' => 'datetime',
            'meta' => 'int'
        ];

        if ($this->validate->validate($option, $types)){
            throw new OptionException('オプションエラー');
        }

        return $this->request('GET', 'posts/all', $option);
    }

    public function suggestPost($url)
    {
        $option = ['url' => $url];
        if ($this->validate->validate($option, ['url' => 'url'])){
            throw new OptionException('オプションエラー');
        }

        return $this->request('GET', 'posts/suggest', $option);
    }

    public function deleteTag($tag)
    {
        $option = ['tag' => $tag];
        if ($this->validate->validate($option, ['tag' => 'tag'])){
            throw new OptionException('オプションエラー');
        }

        return $this->request('GET', 'tags/delete', $option);
    }

    public function renameTag($old, $new)
    {
        $option = ['old' => $old, 'new' => $new];
        if ($this->validate->validate($option, ['old' => 'tag', 'new' => 'tag'])){
            throw new OptionException('オプションエラー');
        }

        return $this->request('GET', 'tags/rename', $option);
    }

    public function getTags()
    {
        return $this->request('GET', 'tags/get');
    }

    public function userSecret()
    {
        return $this->request('GET', 'user/secret');
    }

    public function userToken()
    {
        return $this->request('GET', 'user/api_token');
    }

    public function notesList()
    {
        return $this->request('GET', 'notes/list');
    }

    public function noteById($id)
    {
        if (ctype_xdigit($id) === false){
            throw new OptionException('オプションエラー');
        }

        return $this->request('GET', 'notes/'.$id);
    }
}