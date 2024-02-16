<?php

namespace Tests\Unit\Models;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_settings_creates_on_user_create(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->settings);
        $this->assertTrue($user->settings->defaultCurrency->is(Currency::findByCode('USD')));
    }
}
