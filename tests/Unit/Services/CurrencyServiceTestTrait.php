<?php

namespace Tests\Unit\Services;

use App\Contracts\CurrencyService;

trait CurrencyServiceTestTrait
{
    public function test_fetching_currency_code_list(): void
    {
        $currencyService = $this->getService();
        $codes = $currencyService->fetchCurrencyList()->pluck('code');
        $this->assertTrue($codes->contains('USD'));
    }

    abstract protected function getService(): CurrencyService;
}
