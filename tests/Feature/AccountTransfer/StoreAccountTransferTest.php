<?php

namespace Tests\Feature\AccountTransfer;

use App\Models\Account;
use App\Models\AccountTransfer;
use App\Models\Currency;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreAccountTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_transfer(): void
    {
        $user = User::factory()->create();

        $accountFrom = Account::factory()->create([
            'user_id' => $user->id,
            'currency_id' => Currency::findByCode('USD'),
        ]);
        $accountTo = Account::factory()->create([
            'user_id' => $user->id,
            'currency_id' => Currency::findByCode('USD'),
        ]);

        $response = $this
            ->actingAs($user)
            ->post(
                route('account-transfers.store'),
                [
                    'account_from_id' => $accountFrom->id,
                    'account_to_id' => $accountTo->id,
                    'amount' => 1000,
                    'date' => Carbon::parse('2024-01-01')->format('Y-m-d'),
                    'description' => 'Test description',
                ]
            );

        $response->assertSessionHasNoErrors();
        $this->assertCount(1, AccountTransfer::all());
        tap(
            AccountTransfer::first(),
            function (AccountTransfer $accountTransfer) use ($accountFrom, $accountTo, $user) {
                $this->assertTrue($accountTransfer->user->is($user));
                $this->assertTrue($accountTransfer->accountFrom->is($accountFrom));
                $this->assertTrue($accountTransfer->accountTo->is($accountTo));
                $this->assertEqualsWithDelta(1000, $accountTransfer->amount, 0.0001);
                $this->assertEquals(Carbon::parse('2024-01-01'), $accountTransfer->date);
            }
        );
    }

    public function test_guest_cannot_create_transfer()
    {
        $response = $this
            ->post(
                route('account-transfers.store'),
                $this->validParams()
            );

        $response->assertRedirectToRoute('login');
        $this->assertCount(0, AccountTransfer::all());
    }

    public function test_account_from_is_required()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(
                route('account-transfers.store'),
                $this->validParams(
                    [
                        'account_from_id' => '',
                    ],
                    $user
                )
            );

        $response->assertSessionHasErrors('account_from_id');
        $this->assertCount(0, AccountTransfer::all());
    }

    public function test_user_cannot_transfer_from_others_account()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $accountFrom = Account::factory()->create([
            'user_id' => $otherUser->id,
        ]);
        $accountTo = Account::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(
                route('account-transfers.store'),
                $this->validParams(
                    [
                        'account_from_id' => $accountFrom->id,
                        'account_to_id' => $accountTo->id,
                    ],
                    $user
                )
            );

        $response->assertSessionHasErrors('account_from_id');
        $this->assertCount(0, AccountTransfer::all());
    }

    public function test_account_to_is_required()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(
                route('account-transfers.store'),
                $this->validParams(
                    [
                        'account_to_id' => '',
                    ],
                    $user
                )
            );

        $response->assertSessionHasErrors('account_to_id');
        $this->assertCount(0, AccountTransfer::all());
    }

    public function test_user_cannot_transfer_to_others_account()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $accountFrom = Account::factory()->create([
            'user_id' => $user->id,
        ]);
        $accountTo = Account::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(
                route('account-transfers.store'),
                $this->validParams(
                    [
                        'account_from_id' => $accountFrom->id,
                        'account_to_id' => $accountTo->id,
                    ],
                    $user
                )
            );

        $response->assertSessionHasErrors('account_to_id');
        $this->assertCount(0, AccountTransfer::all());
    }

    public function test_date_is_required()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(
                route('account-transfers.store'),
                $this->validParams(
                    [
                        'date' => '',
                    ],
                    $user
                )
            );

        $response->assertSessionHasErrors('date');
        $this->assertCount(0, AccountTransfer::all());
    }

    public function test_date_must_be_valid_date()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(
                route('account-transfers.store'),
                $this->validParams(
                    [
                        'date' => 'not-valid-date',
                    ],
                    $user
                )
            );

        $response->assertSessionHasErrors('date');
        $this->assertCount(0, AccountTransfer::all());
    }

    public function test_amount_is_required()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(
                route('account-transfers.store'),
                $this->validParams(
                    [
                        'amount' => '',
                    ],
                    $user
                )
            );

        $response->assertSessionHasErrors('amount');
        $this->assertCount(0, AccountTransfer::all());
    }

    public function test_amount_must_be_numeric()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(
                route('account-transfers.store'),
                $this->validParams(
                    [
                        'amount' => 'not-numeric',
                    ],
                    $user
                )
            );

        $response->assertSessionHasErrors('amount');
        $this->assertCount(0, AccountTransfer::all());
    }

    public function test_amount_must_be_greater_than_0()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(
                route('account-transfers.store'),
                $this->validParams(
                    [
                        'amount' => 0,
                    ],
                    $user
                )
            );

        $response->assertSessionHasErrors('amount');
        $this->assertCount(0, AccountTransfer::all());
    }

    public function test_description_is_optional()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(
                route('account-transfers.store'),
                $this->validParams(
                    ['description' => ''],
                    $user
                )
            );

        $response->assertSessionHasNoErrors();
        $this->assertCount(1, AccountTransfer::all());
        $this->assertEquals('', AccountTransfer::first()->description);
    }

    public function test_converted_amount_required_when_currencies_different()
    {
        $user = User::factory()->create();

        $accountFrom = Account::factory()->create([
            'user_id' => $user->id,
            'currency_id' => Currency::findByCode('USD')->id,
        ]);
        $accountTo = Account::factory()->create([
            'user_id' => $user->id,
            'currency_id' => Currency::findByCode('EUR')->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(
                route('account-transfers.store'),
                $this->validParams(
                    [
                        'account_from_id' => $accountFrom->id,
                        'account_to_id' => $accountTo->id,
                        'converted_amount' => '',
                    ],
                    $user
                )
            );

        $response->assertSessionHasErrors('converted_amount');
        $this->assertCount(0, AccountTransfer::all());
    }

    public function test_converted_amount_must_be_numeric()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(
                route('account-transfers.store'),
                $this->validParams(
                    [
                        'converted_amount' => 'not-numeric',
                    ],
                    $user
                )
            );

        $response->assertSessionHasErrors('converted_amount');
        $this->assertCount(0, AccountTransfer::all());
    }

    public function test_converted_amount_must_be_greater_than_0()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(
                route('account-transfers.store'),
                $this->validParams(
                    [
                        'converted_amount' => 0,
                    ],
                    $user
                )
            );

        $response->assertSessionHasErrors('converted_amount');
        $this->assertCount(0, AccountTransfer::all());
    }

    public function test_transfer_actually_affects_accounts_balance()
    {
        $user = User::factory()->create();

        $accountFrom = Account::factory()->create([
            'user_id' => $user->id,
            'currency_id' => Currency::findByCode('USD')->id,
            'balance' => 1234,
        ]);
        $accountTo = Account::factory()->create([
            'user_id' => $user->id,
            'currency_id' => Currency::findByCode('USD')->id,
            'balance' => 0,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(
                route('account-transfers.store'),
                $this->validParams(
                    [
                        'account_from_id' => $accountFrom->id,
                        'account_to_id' => $accountTo->id,
                        'amount' => 1000,
                    ],
                    $user
                )
            );

        $response->assertSessionHasNoErrors();
        $this->assertEqualsWithDelta(234, $accountFrom->fresh()->balance, 0.0001);
        $this->assertEqualsWithDelta(1000, $accountTo->fresh()->balance, 0.0001);
    }

    public function test_transfer_with_different_currencies_charges_in_converted_amount()
    {
        $user = User::factory()->create();

        $accountFrom = Account::factory()->create([
            'user_id' => $user->id,
            'currency_id' => Currency::findByCode('USD')->id,
            'balance' => 1234,
        ]);
        $accountTo = Account::factory()->create([
            'user_id' => $user->id,
            'currency_id' => Currency::findByCode('EUR')->id,
            'balance' => 0,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(
                route('account-transfers.store'),
                $this->validParams(
                    [
                        'account_from_id' => $accountFrom->id,
                        'account_to_id' => $accountTo->id,
                        'amount' => 1000,
                        'converted_amount' => 1111,
                    ],
                    $user
                )
            );

        $response->assertSessionHasNoErrors();
        $this->assertEqualsWithDelta(234, $accountFrom->fresh()->balance, 0.0001);
        $this->assertEqualsWithDelta(1111, $accountTo->fresh()->balance, 0.0001);
    }

    public function validParams(array $overrides = [], User $user = null): array
    {
        $accountFrom = $user
            ? Account::factory()->create(['user_id' => $user->id])
            : Account::factory()->create();

        $accountTo = $user
            ? Account::factory()->create(['user_id' => $user->id])
            : Account::factory()->create();
        return [
            'account_from_id' => $accountFrom->id,
            'account_to_id' => $accountTo->id,
            'amount' => 1000,
            'date' => Carbon::parse('2024-01-01')->format('Y-m-d'),
            'description' => 'Test description',
            ...$overrides,
        ];
    }
}
