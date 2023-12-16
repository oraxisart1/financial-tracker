<?php

namespace Tests\Unit\Services;

use App\Contracts\CurrencyService;
use App\Services\FakeCurrencyService;
use PHPUnit\Framework\TestCase;

class FakeCurrencyServiceTest extends TestCase
{
    use CurrencyServiceTestTrait;

    protected function getService(): CurrencyService
    {
        return new FakeCurrencyService();
    }
}
