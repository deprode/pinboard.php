<?php


use PHPUnit\Framework\TestCase;
use PinboardPHP\Lib\Client;

class ClientTest extends TestCase
{
    public function testLastUpdatePosts()
    {
        $client_mock = $this->createMock(\GuzzleHttp\Client::class);
        $response_mock = new GuzzleHttp\Psr7\Response(200,[],"\"{\"update_time\":\"2020-05-13T15:37:07Z\"}");
        $client_mock->expects($this->any())->method('request')->willReturn($response_mock);

        $client = new Client(API_TOKEN, $client_mock);
        $response = $client->lastUpdatePosts();
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), '"{"update_time":"2020-05-13T15:37:07Z"}');
    }
    }
}
