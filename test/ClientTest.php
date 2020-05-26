<?php


use PHPUnit\Framework\TestCase;
use PinboardPHP\Lib\Client;

class ClientTest extends TestCase
{
    public function testLastUpdatePosts()
    {
        $client_mock = $this->createMock(\GuzzleHttp\Client::class);
        $response_mock = new GuzzleHttp\Psr7\Response(200,[],'{"update_time":"2020-05-13T15:37:07Z"}');
        $client_mock->expects($this->any())->method('request')->willReturn($response_mock);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->lastUpdatePosts();
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), '{"update_time":"2020-05-13T15:37:07Z"}');
    }

    public function testRecentPosts()
    {
        $dummy = '{"date":"2020-05-20T00:54:47Z","user":"deprode","posts":[{"href":"https:\/\/example.com\/","description":"long description","extended":"","meta":"09876543210987654321098765432109","hash":"12345678901234567890123456789012","time":"2020-04-14T11:51:06Z","shared":"yes","toread":"yes","tags":"Testing"}]}';
        $client_mock = $this->createMock(\GuzzleHttp\Client::class);
        $response_mock = new GuzzleHttp\Psr7\Response(200,[],$dummy);
        $client_mock->expects($this->any())->method('request')->willReturn($response_mock);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->recentPosts(['tag' => 'Testing', 'count' => 100]);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $dummy);
    }

    public function testDatesPosts()
    {
        $dummy = '{"user":"deprode","tag":"test","dates":{"2020-05-20":"1","2020-05-13":"4"}}';
        $client_mock = $this->createMock(\GuzzleHttp\Client::class);
        $response_mock = new GuzzleHttp\Psr7\Response(200,[],$dummy);
        $client_mock->expects($this->any())->method('request')->willReturn($response_mock);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->datesPosts(['tag' => 'Testing']);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $dummy);
    }

    public function testNotesList()
    {
        $dummy = '{"count":1,"notes":[{"id":"cf73bfc02e00edaa1e2b","hash":"0bbca3cba9246bbbda2c","title":"Paul Graham on Hirin\' The Ladies","length":"890","created_at":"2011-10-28 13:37:23","updated_at":"2011-10-28 13:37:23"}';
        $client_mock = $this->createMock(\GuzzleHttp\Client::class);
        $response_mock = new GuzzleHttp\Psr7\Response(200,[],$dummy);
        $client_mock->expects($this->any())->method('request')->willReturn($response_mock);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->notesList();
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $dummy);
    }


    public function testNotesId()
    {
        $dummy = '{"id":"cf73bfc02e00edaa1e2b","title":"Paul Graham on Hirin\' The Ladies","created_at":"2011-10-28 13:37:23","updated_at":"2011-10-28 13:37:23","length":556,"text":"[2] One advantage startups have over established companies is that there are no discrimination laws about starting businesses. For example, I would be reluctant to start a startup with a woman who had small children, or was likely to have them soon. ..., you can discriminate on any basis you want about who you start it with.","hash":"0bbca3cba9246bbbda2c"}';
        $client_mock = $this->createMock(\GuzzleHttp\Client::class);
        $response_mock = new GuzzleHttp\Psr7\Response(200,[],$dummy);
        $client_mock->expects($this->any())->method('request')->willReturn($response_mock);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->noteById("cf73bfc02e00edaa1e2b");
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $dummy);
    }
}
