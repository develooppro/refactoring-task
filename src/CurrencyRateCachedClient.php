<?php

declare(strict_types=1);

namespace App;

use GuzzleHttp\ClientInterface;

class CurrencyRateCachedClient implements CurrencyRateClientInterface
{
    private array $rates = [];

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly string $url,
        private readonly string $accessKey
    ) {
    }

    private function loadRates(): void
    {
        $response = $this->httpClient->request('GET', $this->url . '?access_key=' . $this->accessKey);
        if ($response->getStatusCode() !== 200
            || empty($body = (string) $response->getBody())
        ) {
            throw new \Exception("Currency rates are not accessible on the endpoint [$this->url]");
        }

        $decodedResponse = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        if (!array_key_exists('rates', $decodedResponse) || empty($decodedResponse['rates'])) {
            throw new \Exception("Currency rates are not accessible from structure [$body]");
        }

        $this->rates = $decodedResponse['rates'];
    }

    /**
     * Getting currency rate to EUR as default
     *
     * @param string $currencyCode
     *
     * @return string
     * @throws \JsonException
     *
     * @see \App\Tests\Unit\CurrencyRateCachedClientTest::testGetRate
     */
    public function getRate(string $currencyCode): string
    {
        if (empty($this->rates)) {
            $this->loadRates();
        }

        if (!array_key_exists($currencyCode, $this->rates)) {
            throw new \Exception("Rate is not found for the currency code [$currencyCode]");
        }

        return (string) $this->rates[$currencyCode];
    }
}
