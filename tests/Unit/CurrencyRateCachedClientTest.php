<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\CurrencyRateCachedClient;
use Exception;
use Generator;
use GuzzleHttp\ClientInterface;
use JsonException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class CurrencyRateCachedClientTest extends TestCase
{
    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed> $output
     * @return void
     * @throws Exception
     *
     * @dataProvider getRateDataProvider
     *
     * @covers CurrencyRateCachedClient::getRate
     */
    public function testGetRate(array $input, array $output): void
    {
        // Prepare a sample response from the HTTP client for EUR rate
        $responseBody = $input['apiData'];
        $httpClientMock = $this->createMock(ClientInterface::class);

        $responseBodyStream = $this->createMock(StreamInterface::class);
        $responseBodyStream->method('__toString')->willReturn($responseBody);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getBody')->willReturn($responseBodyStream);
        $responseMock->method('getStatusCode')->willReturn(200);
        $httpClientMock->method('request')->willReturn($responseMock);

        // Create the CurrencyRateCachedClient instance with the mock ClientInterface
        $countriesClient = new CurrencyRateCachedClient(
            $httpClientMock,
            'http://example.com/api/rates',
            'your_access_key_here'
        );

        if (!$output['exception']) {
            $rate = $countriesClient->getRate($input['currencyCode']);

            $this->assertEquals($output['rate'], $rate);
        } else {
            $this->expectException($output['exception']::class);
            $this->expectExceptionMessage($output['exception']->getMessage());

            $countriesClient->getRate($input['currencyCode']);
        }
    }

    public function getRateDataProvider(): Generator
    {
        yield 'Success' => [
            'input' => [
                'currencyCode' => 'USD',
                'apiData' => '{"rates":{"EUR":1.0,"USD":1.234,"GBP":0.876},"base":"EUR","date":"2023-07-29"}',
            ],
            'output' => [
                'exception' => null,
                'rate' => '1.234'
            ],
        ];

        yield 'Success for Euro' => [
            'input' => [
                'currencyCode' => 'EUR',
                'apiData' => '{"rates":{"EUR":1.0,"USD":1.234,"GBP":0.876},"base":"EUR","date":"2023-07-29"}',
            ],
            'output' => [
                'exception' => null,
                'rate' => '1'
            ],
        ];

        yield 'Non existing currency' => [
            'input' => [
                'currencyCode' => 'JPY',
                'apiData' => '{"rates":{"EUR":1.0,"USD":1.234,"GBP":0.876},"base":"EUR","date":"2023-07-29"}',
            ],
            'output' => [
                'exception' => new Exception('Rate is not found for the currency code [JPY]'),
                'rate' => null
            ],
        ];

        yield 'Empty response body' => [
            'input' => [
                'currencyCode' => 'JPY',
                'apiData' => '',
            ],
            'output' => [
                'exception' => new Exception(
                    'Currency rates are not accessible on the endpoint [http://example.com/api/rates]'
                ),
                'rate' => null
            ],
        ];

        $responseBody = '{"rates":{"EUR":1.0,"USD":1.234,"GBP":0.876},"base":"EUR","date":"2023-07-29"';
        yield 'Invalid json response' => [
            'input' => [
                'currencyCode' => 'JPY',
                'apiData' => $responseBody,
            ],
            'output' => [
                'exception' => new JsonException(
                    "Syntax error"
                ),
                'rate' => null
            ],
        ];

        $responseBody = '{"rates":{},"base":"EUR","date":"2023-07-29"}';
        yield 'Invalid Json structure' => [
            'input' => [
                'currencyCode' => 'JPY',
                'apiData' => $responseBody,
            ],
            'output' => [
                'exception' => new Exception(
                    "Currency rates are not accessible from structure [$responseBody]"
                ),
                'rate' => null
            ],
        ];
    }
}
