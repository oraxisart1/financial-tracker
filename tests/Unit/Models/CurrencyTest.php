<?php

namespace Tests\Unit\Models;

use App\Models\Currency;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_finds_correct_currency_by_code(): void
    {
        $this->assertEquals('USD', Currency::findByCode('USD')->code);
    }

    public function test_fails_when_code_not_exists()
    {
        $this->expectException(ModelNotFoundException::class);

        Currency::findByCode('NOT-EXISTING-CODE');
    }
}
