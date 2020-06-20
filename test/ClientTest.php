<?php


use PHPUnit\Framework\TestCase;
use PinboardPHP\Lib\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PinboardPHP\Lib\Exception\BadRespnoseException as PinBadResponseException;
use PinboardPHP\Lib\Exception\AuthException;
use PinboardPHP\Lib\Exception\ManyRequestException;
use PinboardPHP\Lib\Exception\OptionException;

class ClientTest extends TestCase
{
    public function getClientMock($response)
    {
        $client_mock = $this->createMock(\GuzzleHttp\Client::class);
        $response_mock = new GuzzleHttp\Psr7\Response(200,[],$response);
        $client_mock->expects($this->any())->method('request')->willReturn($response_mock);
        return $client_mock;
    }

    public function testAuthException()
    {
        $this->expectException(AuthException::class);

        /**@var Psr\Http\Message\RequestInterface */
        $request_mock = $this->createMock(Request::class);
        $client_mock = $this->createMock(\GuzzleHttp\Client::class);
        $response = new Response(401);
        $client_mock->expects($this->any())->method('request')->will($this->throwException(new RequestException('Error', $request_mock, $response)));
        $client = new Client(API_TOKEN, $client_mock);
        $client->lastUpdatePosts();
    }

    public function testManyRequestException()
    {
        $this->expectException(ManyRequestException::class);

        /**@var Psr\Http\Message\RequestInterface */
        $request_mock = $this->createMock(Request::class);
        $client_mock = $this->createMock(\GuzzleHttp\Client::class);
        $response = new Response(429);
        $client_mock->expects($this->any())->method('request')->will($this->throwException(new RequestException('Error', $request_mock, $response)));
        $client = new Client(API_TOKEN, $client_mock);
        $client->recentPosts();
    }

    public function testBadResponseException()
    {
        $this->expectException(PinBadResponseException::class);

        /**@var Psr\Http\Message\RequestInterface */
        $request_mock = $this->createMock(Request::class);
        $client_mock = $this->createMock(\GuzzleHttp\Client::class);
        $response = new Response(503);
        $client_mock->expects($this->any())->method('request')->will($this->throwException(new RequestException('Error', $request_mock, $response)));
        $client = new Client(API_TOKEN, $client_mock);
        $client->lastUpdatePosts();
    }

    public function testOptionException()
    {
        $this->expectException(OptionException::class);

        $client = new Client(API_TOKEN);
        $client->getPost(['meta' => 'foo/bar']);
    }

    public function testFormat()
    {
        $client_mock = $this->getClientMock("{\"update_time\":\"\xEF\xBB\xBF2020-05-13T15:37:07Z\"}");
        $client = new Client(API_TOKEN, $client_mock);
        $result = $client->format($client->lastUpdatePosts());
        $this->assertEquals(['update_time' => '2020-05-13T15:37:07Z'], $result);
    }

    public function testLastUpdatePosts()
    {
        $client_mock = $this->getClientMock('{"update_time":"2020-05-13T15:37:07Z"}');
        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->lastUpdatePosts();
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), '{"update_time":"2020-05-13T15:37:07Z"}');
    }

    public function testRecentPosts()
    {
        $response_body = '{"date":"2020-05-20T00:54:47Z","user":"deprode","posts":[{"href":"https:\/\/example.com\/","description":"long description","extended":"","meta":"09876543210987654321098765432109","hash":"12345678901234567890123456789012","time":"2020-04-14T11:51:06Z","shared":"yes","toread":"yes","tags":"Testing"}]}';
        $client_mock = $this->getClientMock($response_body);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->recentPosts(['tag' => 'Testing', 'count' => 100]);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $response_body);
    }

    public function testDatesPosts()
    {
        $response_body = '{"user":"deprode","tag":"test","dates":{"2020-05-20":"1","2020-05-13":"4"}}';
        $client_mock = $this->getClientMock($response_body);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->datesPosts(['tag' => 'Testing']);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $response_body);
    }

    public function testAddPost()
    {
        $response_body = '{"result":"done"}';
        $client_mock = $this->getClientMock($response_body);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->addPost('http://example.com', 'Example site', ['shared' => 'no']);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $response_body);
    }

    public function testDeletePost()
    {
        $response_body = '{"result":"done"}';
        $client_mock = $this->getClientMock($response_body);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->deletePost('http://example.com');
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $response_body);

        $response_body = '{"result_code":"item not found"}';
        $client_mock = $this->getClientMock($response_body);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->deletePost('http://example.com/not_found');
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $response_body);
    }

    public function testGetPost()
    {
        $response_body = '{"date":"2020-05-01T00:00:00Z","user":"testuser","posts":[{"href":"https:\/\/example.com\/","description":"Example site","extended":"","meta":"92959a96fd69146c5fe7cbde6e5720f2","hash":"54439a52e2efc520d5f9e5e615b89a5d","time":"2020-05-01T00:00:00Z","shared":"yes","toread":"yes","tags":"foo"}]}';
        $client_mock = $this->getClientMock($response_body);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->getPost(['url' => 'https://example.com/']);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $response_body);
    }

    public function testAllPosts()
    {
        $response_body = '[{"href":"https:\/\/example.com\/first-post","description":"testing first site","extended":"test1","meta":"3bad0cf612e5834221d7242b8fb0f2c4","hash":"6cfedbe75f413c56b6ce79e6fa102aba","time":"2020-05-27T03:13:23Z","shared":"yes","toread":"yes","tags":"foo"},{"href":"https:\/\/example.com\/second\/post","description":"testing second site.","extended":"test2","meta":"c86f6de807c5fef2ddda6d2422e12eea","hash":"ca1e6357399774951eed4628d69eb84b","time":"2020-05-27T03:13:21Z","shared":"yes","toread":"yes","tags":"bar"}]';
        $client_mock = $this->getClientMock($response_body);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->allPosts(['start' => 1, 'results' => 2]);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $response_body);
    }

    public function testSuggestPost()
    {
        $response_body = '[{"popular":[]},{"recommended":["bash","commands","Find","hacks"]}]';
        $client_mock = $this->getClientMock($response_body);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->suggestPost('https://example.com/');
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $response_body);
    }

    public function testUserSecret()
    {
        $response_body = '{"result":"6493a84f72d86e7de130"}';
        $client_mock = $this->getClientMock($response_body);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->userSecret();
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $response_body);
    }

    public function testUserToken()
    {
        $response_body = '{"result":"XOG86E7JIYMI"}';
        $client_mock = $this->getClientMock($response_body);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->userToken();
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $response_body);
    }

    public function testDeleteTag()
    {
        $response_body = '{"result":"done"}';
        $client_mock = $this->getClientMock($response_body);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->renameTag('foo', 'bar');
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $response_body);
    }

    public function testRenameTag()
    {
        $response_body = '{"result":"done"}';
        $client_mock = $this->getClientMock($response_body);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->renameTag('foo', 'bar');
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $response_body);
    }

    public function testGetTags()
    {
        $response_body = '{"foo":"11","bar":"4"}';
        $client_mock = $this->getClientMock($response_body);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->getTags();
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $response_body);
    }

    public function testNotesList()
    {
        $response_body = '{"count":1,"notes":[{"id":"cf73bfc02e00edaa1e2b","hash":"0bbca3cba9246bbbda2c","title":"Paul Graham on Hirin\' The Ladies","length":"890","created_at":"2011-10-28 13:37:23","updated_at":"2011-10-28 13:37:23"}]}';
        $client_mock = $this->getClientMock($response_body);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->notesList();
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $response_body);
    }

    public function testNotesId()
    {
        $response_body = '{"id":"cf73bfc02e00edaa1e2b","title":"Paul Graham on Hirin\' The Ladies","created_at":"2011-10-28 13:37:23","updated_at":"2011-10-28 13:37:23","length":556,"text":"[2] One advantage startups have over established companies is that there are no discrimination laws about starting businesses. For example, I would be reluctant to start a startup with a woman who had small children, or was likely to have them soon. ..., you can discriminate on any basis you want about who you start it with.","hash":"0bbca3cba9246bbbda2c"}';
        $client_mock = $this->getClientMock($response_body);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->noteById("cf73bfc02e00edaa1e2b");
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $response_body);
    }
}
