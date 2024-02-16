<?php

namespace Tests\Feature\UserSettings;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateUserSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_their_settings(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($user->settings->defaultCurrency->is(Currency::findByCode('USD')));

        $response = $this->actingAs($user)->patch(
            route('user-settings.update'),
            [
                'currency' => 'EUR',
            ]
        );

        $response->assertSessionHasNoErrors();
        $this->assertTrue($user->settings->fresh()->defaultCurrency->is(Currency::findByCode('EUR')));
    }

    public function test_guest_cannot_update_settings()
    {
        $response = $this->patch(
            route('user-settings.update'),
            [
                'currency' => 'EUR',
            ]
        );

        $response->assertRedirectToRoute('login');
    }

    public function test_currency_is_required()
    {
        $user = User::factory()->create();
        $this->assertTrue($user->settings->defaultCurrency->is(Currency::findByCode('USD')));

        $response = $this->actingAs($user)->patch(
            route('user-settings.update'),
            [
                'currency' => '',
            ]
        );

        $response->assertSessionHasErrors('currency');
        $this->assertTrue($user->settings->fresh()->defaultCurrency->is(Currency::findByCode('USD')));
    }

    public function test_currency_must_be_existing_currency()
    {
        $user = User::factory()->create();
        $this->assertTrue($user->settings->defaultCurrency->is(Currency::findByCode('USD')));

        $response = $this->actingAs($user)->patch(
            route('user-settings.update'),
            [
                'currency' => 'not-existing-currency',
            ]
        );

        $response->assertSessionHasErrors('currency');
        $this->assertTrue($user->settings->fresh()->defaultCurrency->is(Currency::findByCode('USD')));
    }
}
