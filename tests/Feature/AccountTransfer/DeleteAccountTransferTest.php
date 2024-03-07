<?php

namespace Tests\Feature\AccountTransfer;

use App\Models\Account;
use App\Models\AccountTransfer;
use App\Models\Currency;
use App\Models\User;
use App\Services\AccountTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteAccountTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_their_own_account_transfer(): void
    {
        $user = User::factory()->create();
        $accountTransfer = $user->accountTransfers()->save(AccountTransfer::factory()->create());
        $this->assertCount(1, AccountTransfer::all());

        $response = $this->actingAs($user)->from(route('accounts.index'))->delete(
            route(
                'account-transfers.destroy',
                ['account_transfer' => $accountTransfer]
            )
        );

        $response->assertRedirectToRoute('accounts.index');
        $this->assertCount(0, AccountTransfer::all());
    }

    public function test_user_cannot_delete_other_account_transfer(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $accountTransfer = $otherUser->accountTransfers()->save(AccountTransfer::factory()->create());
        $this->assertCount(1, AccountTransfer::all());

        $response = $this->actingAs($user)->delete(
            route(
                'account-transfers.destroy',
                ['account_transfer' => $accountTransfer]
            )
        );

        $response->assertStatus(404);
        $this->assertCount(1, AccountTransfer::all());
    }

    public function test_guest_cannot_delete_any_account_transfer(): void
    {
        $user = User::factory()->create();
        $accountTransfer = $user->accountTransfers()->save(AccountTransfer::factory()->create());
        $this->assertCount(1, AccountTransfer::all());

        $response = $this->delete(
            route(
                'account-transfers.destroy',
                ['account_transfer' => $accountTransfer]
            )
        );

        $response->assertRedirectToRoute('login');
        $this->assertCount(1, AccountTransfer::all());
    }

    public function test_deleting_account_transfer_returns_balance_to_accounts()
    {
        $transferService = new AccountTransferService();

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
        $transferService->transferBetweenAccounts($accountFrom, $accountTo, $transfer);
        $this->assertEqualsWithDelta(0, $accountFrom->fresh()->balance, 0);
        $this->assertEqualsWithDelta(2000, $accountTo->fresh()->balance, 0);

        $this->actingAs($user)->delete(
            route(
                'account-transfers.destroy',
                ['account_transfer' => $transfer]
            )
        );

        $this->assertEqualsWithDelta(1000, $accountFrom->fresh()->balance, 0);
        $this->assertEqualsWithDelta(1000, $accountTo->fresh()->balance, 0);
    }
}
