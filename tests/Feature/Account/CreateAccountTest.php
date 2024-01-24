<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_account(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/accounts', [
            'title' => 'Test Account',
            'currency' => 'USD',
            'balance' => '1000',
            'color' => '#FF0000',
        ]);

        $response->assertRedirectToRoute('accounts.index');
        tap(Account::first(), function (Account $account) use ($user) {
            $this->assertTrue($account->user->is($user));
            $this->assertEquals('Test Account', $account->title);
            $this->assertTrue($account->currency->is(Currency::findByCode('USD')));
            $this->assertEqualsWithDelta(1000, $account->balance, 0.0001);
            $this->assertEquals('#FF0000', $account->color);
        });
    }

    public function test_guest_cannot_create_account(): void
    {
        $response = $this->post('/accounts', [
            'title' => 'Test Account',
            'currency' => 'USD',
            'balance' => '1000',
            'color' => '#FF0000',
        ]);

        $response->assertRedirectToRoute('login');
    }

    public function test_title_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            '/accounts',
            $this->validParams(['title' => ''])
        );

        $response->assertSessionHasErrors('title');
        $this->assertCount(0, Account::all());
    }

    public function test_currency_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            '/accounts',
            $this->validParams(['currency' => ''])
        );

        $response->assertSessionHasErrors('currency');
        $this->assertCount(0, Account::all());
    }

    public function test_currency_must_be_existing_currency_code(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            '/accounts',
            $this->validParams(['currency' => 'not-existing-currency'])
        );

        $response->assertSessionHasErrors('currency');
        $this->assertCount(0, Account::all());
    }

    public function test_balance_is_optional(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            '/accounts',
            $this->validParams(['balance' => ''])
        );

        $response->assertSessionHasNoErrors();
        $this->assertCount(1, Account::all());
        tap(Account::first(), function (Account $account) {
            $this->assertEqualsWithDelta(0, $account->balance, 0.0001);
        });
    }

    public function test_balance_must_be_numeric(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            '/accounts',
            $this->validParams(['balance' => 'not-numeric'])
        );

        $response->assertSessionHasErrors('balance');
        $this->assertCount(0, Account::all());
    }

    public function test_color_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            '/accounts',
            $this->validParams(['color' => ''])
        );

        $response->assertSessionHasErrors('color');
        $this->assertCount(0, Account::all());
    }

    public function test_color_must_be_valid_hex_color(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            '/accounts',
            $this->validParams(['color' => 'not-valid-color'])
        );

        $response->assertSessionHasErrors('color');
        $this->assertCount(0, Account::all());
    }

    private function validParams(array $overrides = []): array
    {
        return [
            'title' => 'Test Title',
            'currency' => 'USD',
            'balance' => '1000',
            'color' => '#FF0000',
            ...$overrides,
        ];
    }
}
