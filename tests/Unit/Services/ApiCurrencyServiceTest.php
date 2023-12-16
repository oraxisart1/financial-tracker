<?php

namespace Tests\Unit\Services;

use App\Contracts\CurrencyService;
use App\Services\ApiCurrencyService;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class ApiCurrencyServiceTest extends TestCase
{
    use CurrencyServiceTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getService(): CurrencyService
    {
        return new ApiCurrencyService(new Client());
    }
}
