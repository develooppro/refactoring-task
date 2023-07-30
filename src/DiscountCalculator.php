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
     *
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
