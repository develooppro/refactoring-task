<?php

declare(strict_types=1);

namespace App;

use GuzzleHttp\ClientInterface;

class CountriesCachedClient implements CountriesClientInterface
{
    private array $countries = [];

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly string $url
    ) {
    }

    private function loadCode(string $bid): void
    {
        $response = $this->httpClient->request('GET', $this->url . $bid);
        if ($response->getStatusCode() !== 200
            || empty($body = (string) $response->getBody())
        ) {
            throw new \Exception("Country for BID [$bid] is not defined");
        }
        $decodedResponse = json_decode($body, true, flags: JSON_THROW_ON_ERROR);

        if (empty($decodedResponse)
            || !array_key_exists('country', $decodedResponse)
            || !array_key_exists('alpha2', $decodedResponse['country'])
            || empty($decodedResponse['country']['alpha2'])
        ) {
            throw new \Exception("Country for BID [$bid] is not defined from the structure [$body]");
        }

        $this->countries[$bid] = $decodedResponse['country']['alpha2'];
    }

    /**
     * @param string $bid
     *
     * @return string
     * @throws \JsonException
     *
     * @see \App\Tests\Unit\CountriesCachedClientTest::testGetCode
     */
    public function getCode(string $bid): string
    {
        if (!array_key_exists($bid, $this->countries)) {
            $this->loadCode($bid);
        }
        return $this->countries[$bid];
    }
}
