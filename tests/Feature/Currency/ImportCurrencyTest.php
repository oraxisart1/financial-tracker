<?php

namespace Tests\Feature\Currency;

use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportCurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_importing_currency_via_cli(): void
    {
        $this->artisan('import-currency');

        $this->assertNotNull(Currency::findByCode('USD'));
        $this->assertEquals('Dollar', Currency::findByCode('USD')->name);
    }

    public function test_not_importing_duplicates()
    {
        $this->artisan('import-currency');

        $count = Currency::count();

        $this->artisan('import-currency');

        $this->assertEquals($count, Currency::count());
    }
}
