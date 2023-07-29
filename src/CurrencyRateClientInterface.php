<?php

namespace App;

interface CurrencyRateClientInterface
{
    public function getRate(string $currencyCode): string;
}