<?php

namespace Tests;

use App\Contracts\CurrencyService;
use App\Services\FakeCurrencyService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(CurrencyService::class, FakeCurrencyService::class);
        $this->artisan('import-currency');
    }
}
