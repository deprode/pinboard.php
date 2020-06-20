<?php

declare(strict_types=1);

namespace PinboardPHP\Lib;

use PinboardPHP\Lib\Exception\BadRespnoseException;
use PinboardPHP\Lib\Exception\AuthException;
use PinboardPHP\Lib\Exception\ManyRequestException;
use PinboardPHP\Lib\Exception\OptionException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

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

    public function request(string $method, string $path, array $user_option = []): ResponseInterface
    {
        $uri = $this->baseurl . $path;
        $option = $user_option + $this->default_option;

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

    public function format(ResponseInterface $response, bool $is_array = true): array
    {
        $contents = $response->getBody()->getContents();
        // remove BOM
        $contents = str_replace("\xEF\xBB\xBF",'',$contents);
        // to array
        $contents = json_decode($contents, $is_array);

        return $contents;
    }

    protected function isAuthError(ResponseInterface $response): bool
    {
        return $response && $response->getStatusCode() === 401;
    }

    protected function isManyRequestError(ResponseInterface $response): bool
    {
        return $response && $response->getStatusCode() === 429;
    }

    protected function isBadResponse(ResponseInterface $response): bool
    {
        return is_null($response) || ($response && $response->getStatusCode() !== 200);
    }

    protected function isDone(array $result = []): bool
    {
        return isset($result['result']) && $result['result'] === 'done';
    }

    public function lastUpdatePosts(): array
    {
        return $this->format($this->request('GET', 'posts/update'));
    }

    public function recentPosts(array $option = []): array
    {
        if ($this->validate->validate($option, ['tag' => 'tag', 'count' => 'integer'])){
            throw new OptionException($this->validate->getErrors());
        }

        return $this->format($this->request('GET', 'posts/recent', $option));
    }

    public function datesPosts(array $option = []): array
    {
        if ($this->validate->validate($option, ['tag' => 'tag'])){
            throw new OptionException($this->validate->getErrors());
        }

        return $this->format($this->request('GET', 'posts/dates', $option));
    }

    public function addPost(string $url, string $description, array $options): bool
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
            throw new OptionException($this->validate->getErrors());
        }

        $result = $this->format($this->request('GET', 'posts/add', $option));
        return $this->isDone($result);
    }

    public function deletePost(string $url): bool
    {
        $option = ['url' => $url];
        if ($this->validate->validate($option, ['url' => 'url'])){
            throw new OptionException($this->validate->getErrors());
        }

        $result = $this->format($this->request('GET', 'posts/delete', $option));

        return $this->isDone($result);
    }

    public function getPost(array $options): array
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
            throw new OptionException($this->validate->getErrors());
        }
        return $this->format($this->request('GET', 'posts/get', $option));
    }

    public function allPosts(array $options = []): array
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
            throw new OptionException($this->validate->getErrors());
        }

        return $this->format($this->request('GET', 'posts/all', $option));
    }

    public function suggestPost(string $url): array
    {
        $option = ['url' => $url];
        if ($this->validate->validate($option, ['url' => 'url'])){
            throw new OptionException($this->validate->getErrors());
        }

        return $this->format($this->request('GET', 'posts/suggest', $option));
    }

    public function deleteTag(string $tag): bool
    {
        $option = ['tag' => $tag];
        if ($this->validate->validate($option, ['tag' => 'tag'])){
            throw new OptionException($this->validate->getErrors());
        }

        $result = $this->format($this->request('GET', 'tags/delete', $option));
        return $this->isDone($result);
    }

    public function renameTag(string $old, string $new): bool
    {
        $option = ['old' => $old, 'new' => $new];
        if ($this->validate->validate($option, ['old' => 'tag', 'new' => 'tag'])){
            throw new OptionException($this->validate->getErrors());
        }

        $result = $this->format($this->request('GET', 'tags/rename', $option));
        return $this->isDone($result);
    }

    public function getTags(): array
    {
        return $this->format($this->request('GET', 'tags/get'));
    }

    public function userSecret(): string
    {
        $result = $this->format($this->request('GET', 'user/secret'));

        return $result['result'] ?? '';
    }

    public function userToken(): string
    {
        $result = $this->format($this->request('GET', 'user/api_token'));

        return $result['result'] ?? '';
    }

    public function notesList(): array
    {
        return $this->format($this->request('GET', 'notes/list'));
    }

    public function noteById(string $id): array
    {
        if (ctype_xdigit($id) === false){
            throw new OptionException($this->validate->getErrors());
        }

        return $this->format($this->request('GET', 'notes/'.$id));
    }
}