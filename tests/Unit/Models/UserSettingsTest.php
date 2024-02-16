<?php

namespace Tests\Unit\Models;

use App\Models\Currency;
use App\Models\User;
use App\Models\UserSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_settings_creates_on_user_create(): void
    {
        $user = User::factory()->create();

        $this->assertCount(1, UserSettings::all());
        tap(UserSettings::first(), function (UserSettings $settings) use ($user) {
            $this->assertTrue($settings->user->is($user));
            $this->assertTrue($settings->defaultCurrency->is(Currency::findByCode('USD')));
        });
    }
}
