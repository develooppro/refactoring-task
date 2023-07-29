<?php

declare(strict_types=1);

namespace App;

use BCMathExtended\BC;

class DiscountCalculator
{
    const EURO_CURRENCY_CODE = 'EUR';

    public function __construct(
        private readonly CountriesClientInterface $countriesClient,
        private readonly CurrencyRateClientInterface $currencyRateClient,
        private readonly array $euCodes,
        private readonly int $calculationPrecision = 10,
        private readonly int $outputPrecision = 2
    ) {
    }

    /**
     * @param Transaction $transaction
     * @return string
     *
     * @see \App\Tests\Unit\DiscountCalculatorTest::testGetDiscount
     */
    public function getDiscount(Transaction $transaction): string
    {
        $amountInEuro = $this->getSumInEuro(
            $transaction->getCurrencyCode(),
            $this->currencyRateClient->getRate($transaction->getCurrencyCode()),
            $transaction->getAmount()
        );
        $discountPercentage = $this->getDiscountPercentage($transaction);

//        echo PHP_EOL;
//        echo 'Country code:' . PHP_EOL;
//        print_r($this->countriesClient->getCode($transaction->getBin()));
//        echo PHP_EOL;
//        echo 'Currency rate:' . PHP_EOL;
//        print_r($this->currencyRateClient->getRate($transaction->getCurrencyCode()));
//        echo PHP_EOL;
//        echo 'Amount:' . PHP_EOL;
//        print_r($transaction->getAmount());
//        echo PHP_EOL;
//        echo 'In Euro:' . PHP_EOL;
//        print_r($amountInEuro);
//        echo PHP_EOL;
//        echo 'Discount:' . PHP_EOL;
//        print_r($discountPercentage);
//        echo PHP_EOL;
//        echo 'Before format:' . PHP_EOL;
//        print_r(bcmul($amountInEuro, $discountPercentage, $this->calculationPrecision));
//        echo PHP_EOL;
//        die();

        return $this->formatDiscount(bcmul($amountInEuro, $discountPercentage, $this->calculationPrecision));
    }

    private function getDiscountPercentage(Transaction $transaction): string
    {
        $isCardFromEU = $this->isFromEU($transaction);
        return ($isCardFromEU ? '0.01' : '0.02');
    }

    private function isFromEU(Transaction $transaction): bool
    {
        $code = $this->countriesClient->getCode($transaction->getBin());
        return in_array($code, $this->euCodes);
    }

    private function getSumInEuro(string $currencyCode, string $rate, string $amount): string
    {
        if ($currencyCode == self::EURO_CURRENCY_CODE || (float) $rate <= 0) {
            return $amount;
        }
        return bcdiv($amount, $rate, $this->calculationPrecision);
    }

    private function formatDiscount(string $discount): string
    {
        return BC::roundUp($discount, $this->outputPrecision);
    }
}
