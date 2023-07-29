<?php

declare(strict_types=1);

namespace App;

class Transaction
{
    public function __construct(
        private readonly string $bin,
        private readonly string $amount,
        private readonly string $currencyCode,
    ) {
    }

    public function getBin(): string
    {
        return $this->bin;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }
}
