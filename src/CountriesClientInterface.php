<?php

namespace App;

interface CountriesClientInterface
{
    public function getCode(string $bid): string;
}