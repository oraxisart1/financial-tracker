<?php

namespace App\Services;

use App\Contracts\CurrencyService;
use Illuminate\Support\Collection;

class FakeCurrencyService implements CurrencyService
{
    public function fetchCurrencyList(): Collection
    {
        return collect([
            ['name' => 'Euro', 'code' => 'EUR'],
            ['name' => 'Dollar', 'code' => 'USD'],
        ]);
    }
}
