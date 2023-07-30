<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\DiscountCalculator;
use App\CountriesClientInterface;
use App\CurrencyRateClientInterface;
use App\Transaction;
use Generator;
use PHPUnit\Framework\TestCase;

class DiscountCalculatorTest extends TestCase
{
    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed> $output
     * @return void
     *
     * @dataProvider getDiscountDataProvider
     *
     * @covers DiscountCalculator::getDiscount
     */
    public function testGetDiscount(array $input, array $output): void
    {
        // Mocking the dependencies
        $countriesClientMock = $this->createMock(CountriesClientInterface::class);
        $currencyRateClientMock = $this->createMock(CurrencyRateClientInterface::class);

        // Assume the EU country codes array contains 'FR' and 'DE'
        $euCodes = ['FR', 'DE', 'DK', 'LT'];

        // Create a test transaction with a country code in EU
        $transaction = $input['transaction'];

        // Configure the mocked methods for the test transaction
        $countriesClientMock
            ->method('getCode')
            ->with($transaction->getBin())
            ->willReturn($input['countryCode']);

        $currencyRateClientMock
            ->method('getRate')
            ->with($transaction->getCurrencyCode())
            ->willReturn($input['currencyRate']); // Assuming 1 EUR = 1 EUR

        // Create the DiscountCalculator with mocked dependencies
        $discountCalculator = new DiscountCalculator(
            $countriesClientMock,
            $currencyRateClientMock,
            $euCodes
        );

        // The discount for EU transactions should be 1% of the amount
        $this->assertSame($output['discount'], $discountCalculator->getDiscount($transaction));
    }

    public function getDiscountDataProvider(): Generator
    {
        yield 'With BIN from EU and currency EUR' => [
            'input' => [
                'transaction' => new Transaction('45717360', '100.00', 'EUR'),
                'countryCode' => 'LT',
                'currencyRate' => '1',
            ],
            'output' => [
                'discount' => '1'
            ],
        ];

        yield 'With BIN from EU and currency non EUR' => [
            'input' => [
                'transaction' => new Transaction('516793', '50.00', 'USD'),
                'countryCode' => 'LT',
                'currencyRate' => '1.103139',
            ],
            'output' => [
                'discount' => '0.46' // Rounded from 0.4532520380
            ],
        ];

        yield 'With BIN from non EU and currency non EUR' => [
            'input' => [
                'transaction' => new Transaction('45417360', '10000.00', 'JPY'),
                'countryCode' => 'JP',
                'currencyRate' => '155.702593',
            ],
            'output' => [
                'discount' => '1.29' // Rounded from 1.2845001238 - 2% for non EU countries
            ],
        ];

        yield 'With BIN from non EU and currency EUR' => [
            'input' => [
                'transaction' => new Transaction('4745030', '100.00', 'EUR'),
                'countryCode' => 'GB',
                'currencyRate' => '1',
            ],
            'output' => [
                'discount' => '2' // Rounded from 2% for non EU countries
            ],
        ];
    }
}