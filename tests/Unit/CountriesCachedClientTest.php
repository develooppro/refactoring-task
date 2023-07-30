<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\CountriesCachedClient;
use Exception;
use Generator;
use JsonException;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class CountriesCachedClientTest extends TestCase
{
    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed> $output
     * @return void
     * @throws JsonException
     *
     * @dataProvider getCodeDataProvider
     *
     * @covers CountriesCachedClient::getCode
     */
    public function testGetCode(array $input, array $output): void
    {
        $responseBody = $input['apiData'];
        $httpClientMock = $this->createMock(ClientInterface::class);

        $responseBodyStream = $this->createMock(StreamInterface::class);
        $responseBodyStream->method('__toString')->willReturn($responseBody);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getBody')->willReturn($responseBodyStream);
        $responseMock->method('getStatusCode')->willReturn(200);
        $httpClientMock->method('request')->willReturn($responseMock);

        $countriesClient = new CountriesCachedClient($httpClientMock, 'http://example.com/');

        if (!$output['exception']) {
            $code = $countriesClient->getCode($input['bid']);

            $this->assertEquals($output['code'], $code);
        } else {
            $this->expectException($output['exception']::class);
            $this->expectExceptionMessage($output['exception']->getMessage());

            $countriesClient->getCode($input['bid']);
        }
    }

    public function getCodeDataProvider(): Generator
    {
        yield 'Success' => [
            'input' => [
                'bid' => '123',
                'apiData' => '{"country": {"alpha2": "US"}}',
            ],
            'output' => [
                'exception' => null,
                'code' => 'US'
            ],
        ];

        yield 'Empty response' =>[
            'input' => [
                'bid' => '123',
                'apiData' => '',
            ],
            'output' => [
                'exception' => new Exception('Country for BID [123] is not defined'),
                'code' => null,
            ],
        ];

        $apiData = '{"country": {}}';
        yield 'Invalid response data' => [
            'input' => [
                'bid' => '123',
                'apiData' => $apiData,
            ],
            'output' => [
                'apiData' => $apiData,
                'exception' =>
                    new Exception("Country for BID [123] is not defined from the structure [$apiData]"),
                'code' => 'US',
            ],
        ];

        yield 'Invalid json' => [
            'input' => [
                'bid' => '123',
                'apiData' => '{"country": {}',
            ],
            'output' => [
                'apiData' => $apiData,
                'exception' => new JsonException("Syntax error"),
                'code' => 'US',
            ],
        ];
    }
}
