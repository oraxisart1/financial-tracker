<?php

namespace Tests\Feature\AccountTransfer;

use App\Models\Account;
use App\Models\AccountTransfer;
use App\Models\Currency;
use App\Models\User;
use App\Services\AccountTransferService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UpdateAccountTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_their_own_transfer(): void
    {
        $user = User::factory()->create();
        $accountFrom = $user->accounts()->save(
            Account::factory()->create([
                'balance' => 1000,
                'currency_id' => Currency::findByCode('USD')->id,
            ])
        );
        $accountTo = $user->accounts()->save(
            Account::factory()->create([
                'balance' => 1000,
                'currency_id' => Currency::findByCode('EUR')->id,
            ])
        );

        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'amount' => 1000,
                'converted_amount' => 1100,
                'date' => Carbon::parse('2024-01-01'),
                'description' => 'Old description',
            ])
        );

        (new AccountTransferService())->transferBetweenAccounts($accountFrom, $accountTo, $transfer);
        $this->assertEqualsWithDelta(0, $accountFrom->fresh()->balance, 0);
        $this->assertEqualsWithDelta(2100, $accountTo->fresh()->balance, 0);

        $response = $this->actingAs($user)->from(route('accounts.index'))->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            [
                'account_from_id' => $accountFrom->id,
                'account_to_id' => $accountTo->id,
                'date' => '2024-02-01',
                'description' => 'New description',
                'amount' => 500,
                'converted_amount' => 550,
            ]
        );

        $response->assertRedirectToRoute('accounts.index');
        $response->assertSessionHasNoErrors();

        tap($transfer->fresh(), function (AccountTransfer $transfer) use ($accountFrom, $accountTo, $user) {
            $this->assertTrue($transfer->user->is($user));
            $this->assertTrue($transfer->accountFrom->is($accountFrom));
            $this->assertTrue($transfer->accountTo->is($accountTo));
            $this->assertEquals(Carbon::parse('2024-02-01'), $transfer->date);
            $this->assertEquals('New description', $transfer->description);
            $this->assertEqualsWithDelta(500, $transfer->amount, 0);
            $this->assertEqualsWithDelta(550, $transfer->converted_amount, 0);
            $this->assertEqualsWithDelta(500, $transfer->accountFrom->fresh()->balance, 0);
            $this->assertEqualsWithDelta(1550, $transfer->accountTo->fresh()->balance, 0);
        });
    }

    public function test_user_cannot_update_other_transfer(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $accountFrom = $user->accounts()->save(Account::factory()->create());
        $accountTo = $user->accounts()->save(Account::factory()->create());
        $transfer = $otherUser->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'account_from_id' => $accountFrom->id,
                'account_to_id' => $accountTo->id,
                'date' => Carbon::parse('2024-01-01'),
                'description' => 'Old description',
                'amount' => 1000,
                'converted_amount' => 1000,
            ])
        );

        $response = $this->actingAs($user)->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            [
                'account_from_id' => Account::factory()->create()->id,
                'account_to_id' => Account::factory()->create()->id,
                'date' => '2024-02-01',
                'description' => 'New description',
                'amount' => 500,
                'converted_amount' => 550,
            ]
        );

        $response->assertNotFound();
        tap($transfer->fresh(), function (AccountTransfer $transfer) use ($accountFrom, $accountTo) {
            $this->assertTrue($transfer->accountFrom->is($accountFrom));
            $this->assertTrue($transfer->accountTo->is($accountTo));
            $this->assertEquals(Carbon::parse('2024-01-01'), $transfer->date);
            $this->assertEquals('Old description', $transfer->description);
            $this->assertEqualsWithDelta(1000, $transfer->amount, 0);
            $this->assertEqualsWithDelta(1000, $transfer->converted_amount, 0);
        });
    }

    public function test_guest_cannot_update_any_transfer(): void
    {
        $user = User::factory()->create();
        $accountFrom = $user->accounts()->save(Account::factory()->create());
        $accountTo = $user->accounts()->save(Account::factory()->create());
        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'account_from_id' => $accountFrom->id,
                'account_to_id' => $accountTo->id,
                'date' => Carbon::parse('2024-01-01'),
                'description' => 'Old description',
                'amount' => 1000,
                'converted_amount' => 1000,
            ])
        );

        $response = $this->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            [
                'account_from_id' => Account::factory()->create()->id,
                'account_to_id' => Account::factory()->create()->id,
                'date' => '2024-02-01',
                'description' => 'New description',
                'amount' => 500,
                'converted_amount' => 550,
            ]
        );

        $response->assertRedirectToRoute('login');
        tap($transfer->fresh(), function (AccountTransfer $transfer) use ($accountFrom, $accountTo) {
            $this->assertTrue($transfer->accountFrom->is($accountFrom));
            $this->assertTrue($transfer->accountTo->is($accountTo));
            $this->assertEquals(Carbon::parse('2024-01-01'), $transfer->date);
            $this->assertEquals('Old description', $transfer->description);
            $this->assertEqualsWithDelta(1000, $transfer->amount, 0);
            $this->assertEqualsWithDelta(1000, $transfer->converted_amount, 0);
        });
    }

    public function test_updating_transfer_accounts()
    {
        $user = User::factory()->create();
        $accountFrom = $user->accounts()->save(
            Account::factory()->create([
                'balance' => 1000,
                'currency_id' => Currency::findByCode('USD')->id,
            ])
        );
        $accountTo = $user->accounts()->save(
            Account::factory()->create([
                'balance' => 1000,
                'currency_id' => Currency::findByCode('USD')->id,
            ])
        );

        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'amount' => 1000,
                'converted_amount' => 1000,
            ])
        );

        (new AccountTransferService())->transferBetweenAccounts($accountFrom, $accountTo, $transfer);
        $this->assertEqualsWithDelta(0, $accountFrom->fresh()->balance, 0);
        $this->assertEqualsWithDelta(2000, $accountTo->fresh()->balance, 0);

        $newAccountFrom = $user->accounts()->save(
            Account::factory()->create([
                'balance' => 1000,
                'currency_id' => Currency::findByCode('USD')->id,
            ])
        );
        $newAccountTo = $user->accounts()->save(
            Account::factory()->create([
                'balance' => 1000,
                'currency_id' => Currency::findByCode('USD')->id,
            ])
        );

        $this->actingAs($user)->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            $this->validParams([
                'account_from_id' => $newAccountFrom->id,
                'account_to_id' => $newAccountTo->id,
                'amount' => 1000,
                'converted_amount' => 1000,
            ])
        );

        $this->assertEqualsWithDelta(1000, $accountFrom->fresh()->balance, 0);
        $this->assertEqualsWithDelta(1000, $accountTo->fresh()->balance, 0);
        $this->assertEqualsWithDelta(0, $newAccountFrom->fresh()->balance, 0);
        $this->assertEqualsWithDelta(2000, $newAccountTo->fresh()->balance, 0);
        tap($transfer->fresh(), function (AccountTransfer $transfer) use ($newAccountFrom, $newAccountTo) {
            $this->assertTrue($transfer->accountFrom->is($newAccountFrom));
            $this->assertTrue($transfer->accountTo->is($newAccountTo));
        });
    }

    public function test_account_from_is_required()
    {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'account_from_id' => $account->id,
            ])
        );

        $response = $this->actingAs($user)->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            $this->validParams([
                'account_from_id' => '',
            ])
        );

        $response->assertSessionHasErrors('account_from_id');
        $this->assertTrue($transfer->fresh()->accountFrom->is($account));
    }

    public function test_account_from_must_be_existing_account()
    {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'account_from_id' => $account->id,
            ])
        );

        $response = $this->actingAs($user)->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            $this->validParams([
                'account_from_id' => 9999,
            ])
        );

        $response->assertSessionHasErrors('account_from_id');
        $this->assertTrue($transfer->fresh()->accountFrom->is($account));
    }

    public function test_user_cannot_transfer_from_other_account()
    {
        $user = User::factory()->create();
        $account = $user->accounts()->save(Account::factory()->create());
        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'account_from_id' => $account->id,
            ])
        );
        $otherAccount = User::factory()->create()->accounts()->save(Account::factory()->create());

        $response = $this->actingAs($user)->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            $this->validParams([
                'account_from_id' => $otherAccount->id,
            ])
        );

        $response->assertSessionHasErrors('account_from_id');
        $this->assertTrue($transfer->fresh()->accountFrom->is($account));
    }

    public function test_account_to_is_required()
    {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'account_to_id' => $account->id,
            ])
        );

        $response = $this->actingAs($user)->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            $this->validParams([
                'account_to_id' => '',
            ])
        );

        $response->assertSessionHasErrors('account_to_id');
        $this->assertTrue($transfer->fresh()->accountTo->is($account));
    }

    public function test_account_to_must_be_existing_account()
    {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'account_to_id' => $account->id,
            ])
        );

        $response = $this->actingAs($user)->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            $this->validParams([
                'account_to_id' => 9999,
            ])
        );

        $response->assertSessionHasErrors('account_to_id');
        $this->assertTrue($transfer->fresh()->accountTo->is($account));
    }

    public function test_user_cannot_transfer_to_other_account()
    {
        $user = User::factory()->create();
        $account = $user->accounts()->save(Account::factory()->create());
        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'account_to_id' => $account->id,
            ])
        );
        $otherAccount = User::factory()->create()->accounts()->save(Account::factory()->create());

        $response = $this->actingAs($user)->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            $this->validParams([
                'account_to_id' => $otherAccount->id,
            ])
        );

        $response->assertSessionHasErrors('account_to_id');
        $this->assertTrue($transfer->fresh()->accountTo->is($account));
    }

    public function test_amount_is_required()
    {
        $user = User::factory()->create();
        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'amount' => 1000,
            ])
        );

        $response = $this->actingAs($user)->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            $this->validParams([
                'amount' => '',
            ])
        );

        $response->assertSessionHasErrors('amount');
        $this->assertEqualsWithDelta(1000, $transfer->fresh()->amount, 0);
    }

    public function test_amount_must_be_numberic()
    {
        $user = User::factory()->create();
        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'amount' => 1000,
            ])
        );

        $response = $this->actingAs($user)->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            $this->validParams([
                'amount' => 'not-numeric',
            ])
        );

        $response->assertSessionHasErrors('amount');
        $this->assertEqualsWithDelta(1000, $transfer->fresh()->amount, 0);
    }

    public function test_amount_must_be_greater_than_0()
    {
        $user = User::factory()->create();
        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'amount' => 1000,
            ])
        );

        $response = $this->actingAs($user)->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            $this->validParams([
                'amount' => 0,
            ])
        );

        $response->assertSessionHasErrors('amount');
        $this->assertEqualsWithDelta(1000, $transfer->fresh()->amount, 0);
    }

    public function test_converted_amount_options_when_currencies_same()
    {
        $user = User::factory()->create();
        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'amount' => 1000,
            ])
        );
        $accountFrom = $user->accounts()->save(
            Account::factory()->create([
                'currency_id' => Currency::findByCode('USD'),
            ])
        );
        $accountTo = $user->accounts()->save(
            Account::factory()->create([
                'currency_id' => Currency::findByCode('USD'),
            ])
        );

        $response = $this->actingAs($user)->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            $this->validParams([
                'converted_amount' => '',
                'account_from_id' => $accountFrom->id,
                'account_to_id' => $accountTo->id,
            ])
        );

        $response->assertSessionHasNoErrors();
    }

    public function test_converted_amount_required_when_currencies_different()
    {
        $user = User::factory()->create();
        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'converted_amount' => 1000,
            ])
        );
        $accountFrom = $user->accounts()->save(
            Account::factory()->create([
                'currency_id' => Currency::findByCode('USD'),
            ])
        );
        $accountTo = $user->accounts()->save(
            Account::factory()->create([
                'currency_id' => Currency::findByCode('EUR'),
            ])
        );

        $response = $this->actingAs($user)->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            $this->validParams([
                'converted_amount' => '',
                'account_from_id' => $accountFrom->id,
                'account_to_id' => $accountTo->id,
            ])
        );

        $response->assertSessionHasErrors('converted_amount');
        $this->assertEqualsWithDelta(1000, $transfer->fresh()->converted_amount, 0);
    }

    public function test_converted_amount_must_be_numberic()
    {
        $user = User::factory()->create();
        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'converted_amount' => 1000,
            ])
        );

        $response = $this->actingAs($user)->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            $this->validParams([
                'converted_amount' => 'not-numeric',
            ])
        );

        $response->assertSessionHasErrors('converted_amount');
        $this->assertEqualsWithDelta(1000, $transfer->fresh()->converted_amount, 0);
    }

    public function test_converted_amount_must_be_greater_than_0()
    {
        $user = User::factory()->create();
        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'converted_amount' => 1000,
            ])
        );

        $response = $this->actingAs($user)->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            $this->validParams([
                'converted_amount' => 0,
            ])
        );

        $response->assertSessionHasErrors('converted_amount');
        $this->assertEqualsWithDelta(1000, $transfer->fresh()->converted_amount, 0);
    }

    public function test_date_is_required()
    {
        $user = User::factory()->create();
        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'date' => Carbon::parse('2024-01-01'),
            ])
        );

        $response = $this->actingAs($user)->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            $this->validParams([
                'date' => '',
            ])
        );

        $response->assertSessionHasErrors('date');
        $this->assertEquals(Carbon::parse('2024-01-01'), $transfer->fresh()->date);
    }

    public function test_date_must_be_valid_date()
    {
        $user = User::factory()->create();
        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'date' => Carbon::parse('2024-01-01'),
            ])
        );

        $response = $this->actingAs($user)->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            $this->validParams([
                'date' => 'not-valid-date',
            ])
        );

        $response->assertSessionHasErrors('date');
        $this->assertEquals(Carbon::parse('2024-01-01'), $transfer->fresh()->date);
    }

    public function test_description_is_optional()
    {
        $user = User::factory()->create();
        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create([
                'description' => 'Old description',
            ])
        );

        $response = $this->actingAs($user)->patch(
            route(
                'account-transfers.update',
                ['account_transfer' => $transfer]
            ),
            $this->validParams([
                'description' => '',
            ])
        );

        $response->assertSessionHasNoErrors();
        $this->assertNull($transfer->fresh()->description);
    }

    private function validParams(array $overrides = []): array
    {
        $accountAttributes = [];
        if (Auth::user()) {
            $accountAttributes['user_id'] = Auth::user()->id;
        }

        return [
            'account_from_id' => Account::factory()->create($accountAttributes)->id,
            'account_to_id' => Account::factory()->create($accountAttributes)->id,
            'date' => '2024-02-01',
            'description' => 'New description',
            'amount' => 1000,
            'converted_amount' => 1000,
            ...$overrides,
        ];
    }
}
