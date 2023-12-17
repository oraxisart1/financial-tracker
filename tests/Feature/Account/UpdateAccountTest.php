<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_their_own_account(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
            'currency_id' => Currency::findByCode('USD')->id,
            'balance' => '1000',
            'color' => '#FF0000',
        ]);

        $response = $this->actingAs($user)->from(route('accounts.index'))->patch(
            route('accounts.update', ['account' => $account]),
            [
                'title' => 'New Title',
                'currency' => 'EUR',
                'balance' => '2000',
                'color' => '#0000FF',
            ]
        );

        $response->assertRedirectToRoute('accounts.index');
        tap($account->fresh(), function (Account $account) {
            $this->assertEquals('New Title', $account->title);
            $this->assertTrue($account->currency->is(Currency::findByCode('EUR')));
            $this->assertEqualsWithDelta(2000, $account->balance, 0.0001);
            $this->assertEquals('#0000FF', $account->color);
        });
    }

    public function test_user_cannot_update_other_account()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $otherUser->id,
            'title' => 'Old Title',
            'currency_id' => Currency::findByCode('USD')->id,
            'balance' => '1000',
            'color' => '#FF0000',
        ]);

        $response = $this->actingAs($user)->patch(
            route('accounts.update', ['account' => $account]),
            [
                'title' => 'New Title',
                'currency' => 'EUR',
                'balance' => '2000',
                'color' => '#0000FF',
            ]
        );

        $response->assertStatus(404);
        tap($account->fresh(), function (Account $account) {
            $this->assertEquals('Old Title', $account->title);
            $this->assertTrue($account->currency->is(Currency::findByCode('USD')));
            $this->assertEqualsWithDelta(1000, $account->balance, 0.0001);
            $this->assertEquals('#FF0000', $account->color);
        });
    }

    public function test_guest_cannot_update_any_account()
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
            'currency_id' => Currency::findByCode('USD')->id,
            'balance' => '1000',
            'color' => '#FF0000',
        ]);

        $response = $this->patch(
            route('accounts.update', ['account' => $account]),
            [
                'title' => 'New Title',
                'currency' => 'EUR',
                'balance' => '2000',
                'color' => '#0000FF',
            ]
        );

        $response->assertRedirectToRoute('login');
        tap($account->fresh(), function (Account $account) {
            $this->assertEquals('Old Title', $account->title);
            $this->assertTrue($account->currency->is(Currency::findByCode('USD')));
            $this->assertEqualsWithDelta(1000, $account->balance, 0.0001);
            $this->assertEquals('#FF0000', $account->color);
        });
    }

    public function test_title_is_required(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
        ]);

        $response = $this->actingAs($user)->patch(
            route('accounts.update', ['account' => $account]),
            $this->validParams([
                'title' => '',
            ])
        );

        $response->assertSessionHasErrors('title');
        tap($account->fresh(), function (Account $account) {
            $this->assertEquals('Old Title', $account->title);
        });
    }

    public function test_currency_is_required(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'currency_id' => Currency::findByCode('USD')->id,
        ]);

        $response = $this->actingAs($user)->patch(
            route('accounts.update', ['account' => $account]),
            $this->validParams([
                'currency' => '',
            ])
        );

        $response->assertSessionHasErrors('currency');
        tap($account->fresh(), function (Account $account) {
            $this->assertTrue($account->currency->is(Currency::findByCode('USD')));
        });
    }

    public function test_currency_must_be_existing_currency_code(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'currency_id' => Currency::findByCode('USD')->id,
        ]);

        $response = $this->actingAs($user)->patch(
            route('accounts.update', ['account' => $account]),
            $this->validParams([
                'currency' => 'NOT-EXISTING-CURRENCY',
            ])
        );

        $response->assertSessionHasErrors('currency');
        tap($account->fresh(), function (Account $account) {
            $this->assertTrue($account->currency->is(Currency::findByCode('USD')));
        });
    }

    public function test_balance_is_required(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'balance' => 1000,
        ]);

        $response = $this->actingAs($user)->patch(
            route('accounts.update', ['account' => $account]),
            $this->validParams([
                'balance' => '',
            ])
        );

        $response->assertSessionHasErrors('balance');
        tap($account->fresh(), function (Account $account) {
            $this->assertEqualsWithDelta(1000, $account->balance, 0.0001);
        });
    }

    public function test_balance_must_be_numeric(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'balance' => 1000,
        ]);

        $response = $this->actingAs($user)->patch(
            route('accounts.update', ['account' => $account]),
            $this->validParams([
                'balance' => 'not-numeric',
            ])
        );

        $response->assertSessionHasErrors('balance');
        tap($account->fresh(), function (Account $account) {
            $this->assertEqualsWithDelta(1000, $account->balance, 0.0001);
        });
    }

    public function test_color_is_required(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'color' => '#FF0000',
        ]);

        $response = $this->actingAs($user)->patch(
            route('accounts.update', ['account' => $account]),
            $this->validParams([
                'color' => '',
            ])
        );

        $response->assertSessionHasErrors('color');
        tap($account->fresh(), function (Account $account) {
            $this->assertEquals('#FF0000', $account->color);
        });
    }

    public function test_color_must_be_valid_hex_color(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'color' => '#FF0000',
        ]);

        $response = $this->actingAs($user)->patch(
            route('accounts.update', ['account' => $account]),
            $this->validParams([
                'color' => 'not-valid-color',
            ])
        );

        $response->assertSessionHasErrors('color');
        tap($account->fresh(), function (Account $account) {
            $this->assertEquals('#FF0000', $account->color);
        });
    }

    private function validParams(array $overrides = []): array
    {
        return [
            'title' => 'New Title',
            'currency' => 'EUR',
            'balance' => '2000',
            'color' => '#0000FF',
            ...$overrides,
        ];
    }
}
