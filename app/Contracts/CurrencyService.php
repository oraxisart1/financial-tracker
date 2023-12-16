<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface CurrencyService
{
    public function fetchCurrencyList(): Collection;
}
