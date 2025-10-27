<?php

namespace Cloudmazing\KvkApi\Tests\Unit;

use GuzzleHttp\ClientInterface;
use Cloudmazing\KvkApi\Client;
use Cloudmazing\KvkApi\Exceptions\KvkApiException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ClientTest extends TestCase {
    /** @var ClientInterface&MockInterface */
    private $httpClient;
    private Client $client;

    protected function setUp(): void {
        parent::setUp();
        /** @var ClientInterface&MockInterface */
        $this->httpClient = Mockery::mock(ClientInterface::class);
        $this->client = new Client($this->httpClient);
    }

    public function testCanSearchCompaniesByName() {
        /** @var ResponseInterface&MockInterface */
        $mockResponse = Mockery::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getBody->getContents')
            ->once()
            ->andReturn(json_encode([
                'resultaten' => [
                    [
                        'kvkNummer' => '12345678',
                        'links' => [
                            ['rel' => 'self', 'href' => 'https://api.kvk.nl/api/v2/zoeken/12345678']
                        ]
                    ]
                ]
            ]));

        /** @var ResponseInterface&MockInterface */
        $mockDetailResponse = Mockery::mock(ResponseInterface::class);
        $mockDetailResponse->shouldReceive('getBody->getContents')
            ->once()
            ->andReturn(json_encode([
                'kvkNummer' => '12345678',
                'naam' => 'Test BV',
                'websites' => ['www.test.nl'],
                'adres' => [
                    [
                        'type' => 'bezoekadres',
                        'straatnaam' => 'Teststraat',
                        'huisnummer' => '1',
                        'postcode' => '1234AB',
                        'plaats' => 'Amsterdam',
                        'land' => 'Nederland'
                    ]
                ]
            ]));

        $this->httpClient->shouldReceive('request')
            ->twice()
            ->andReturn($mockResponse, $mockDetailResponse);

        $result = $this->client->search('Test BV');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('12345678', $result[0]->getKvkNumber());
        $this->assertEquals('Test BV', $result[0]->getTradeName());
        $this->assertEquals(['www.test.nl'], $result[0]->getWebsites());
    }

    public function testCanGetCompanyBaseProfile() {
        /** @var ResponseInterface&MockInterface */
        $mockResponse = Mockery::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getBody->getContents')
            ->once()
            ->andReturn(json_encode([
                'kvkNummer' => '12345678',
                'handelsnaam' => 'Test BV',
                'websites' => ['www.test.nl'],
                'adressen' => [
                    [
                        'type' => 'bezoekadres',
                        'straatnaam' => 'Teststraat',
                        'huisnummer' => '1',
                        'postcode' => '1234AB',
                        'plaats' => 'Amsterdam',
                        'land' => 'Nederland'
                    ]
                ]
            ]));

        $this->httpClient->shouldReceive('request')
            ->once()
            ->andReturn($mockResponse);

        $result = $this->client->getBaseProfile('12345678');

        $this->assertEquals('12345678', $result->getKvkNumber());
        $this->assertEquals('Test BV', $result->getTradeName());
        $this->assertEquals(['www.test.nl'], $result->getWebsites());
    }

    public function testThrowsExceptionOnInvalidResponse() {
        /** @var ResponseInterface&MockInterface */
        $mockResponse = Mockery::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getBody->getContents')
            ->once()
            ->andReturn('invalid json');

        $this->httpClient->shouldReceive('request')
            ->once()
            ->andReturn($mockResponse);

        $this->expectException(KvkApiException::class);
        $this->client->search('Test BV');
    }

    protected function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }
}
