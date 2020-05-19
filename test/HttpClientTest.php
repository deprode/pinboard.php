<?php


use PinboardPHP\Lib\HttpClient;
use PHPUnit\Framework\TestCase;

class HttpClientTest extends TestCase
{
    public function testRequest()
    {
        $client_mock = $this->createMock(\GuzzleHttp\Client::class);
        $response_mock = new GuzzleHttp\Psr7\Response(200,[],"\"{\"update_time\":\"2020-05-13T15:37:07Z\"}");
        $client_mock->expects($this->any())->method('request')->willReturn($response_mock);

        $client = new HttpClient($client_mock);
        $response = $client->request('GET', 'posts/update',[]);

        $this->assertEquals('"{"update_time":"2020-05-13T15:37:07Z"}', $response->getBody()->getContents());
    }
}
