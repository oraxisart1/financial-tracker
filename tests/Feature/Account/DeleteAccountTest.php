<?php

namespace Tests\Feature\Account;

use App\Events\AccountDeleted;
use App\Models\Account;
use App\Models\AccountTransfer;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_their_own_account(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->save(Account::factory()->make());
        $this->assertCount(1, $user->accounts);

        $response = $this->from(route('accounts.index'))->actingAs($user)->delete(
            route(
                'accounts.destroy',
                ['account' => $account]
            )
        );

        $response->assertRedirectToRoute('accounts.index');
        $this->assertCount(0, $user->fresh()->accounts);
    }

    public function test_deleting_account_with_cascade_removes_account_completely(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->save(Account::factory()->make());
        $this->assertCount(1, $user->accounts()->withTrashed()->get());

        $this->actingAs($user)->delete(
            route('accounts.destroy', [
                'account' => $account,
                'mode' => AccountService::DELETE_CASCADE_MODE,
            ])
        );

        $this->assertCount(0, $user->accounts()->withTrashed()->get());
    }

    public function test_deleting_account_without_cascade_removes_account_softly(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->save(Account::factory()->make());
        $this->assertCount(0, $user->accounts()->onlyTrashed()->get());

        $this->actingAs($user)->delete(
            route('accounts.destroy', [
                'account' => $account,
                'mode' => AccountService::DELETE_ACCOUNT_ONLY_MODE,
            ])
        );

        $this->assertCount(1, $user->accounts()->onlyTrashed()->get());
    }

    public function test_cascade_deleting_deletes_all_transactions_and_transfers(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->save(Account::factory()->make());
        $account->transactions()->saveMany(Transaction::factory(5)->create());
        $account->transfersFrom()->saveMany(AccountTransfer::factory(5)->create());
        $account->transfersTo()->saveMany(AccountTransfer::factory(5)->create());
        $this->assertCount(5, Transaction::all());
        $this->assertCount(10, AccountTransfer::all());

        $this->actingAs($user)->delete(
            route(
                'accounts.destroy',
                [
                    'account' => $account,
                    'mode' => AccountService::DELETE_CASCADE_MODE,
                ]
            )
        );

        $this->assertCount(0, Transaction::all());
        $this->assertCount(0, AccountTransfer::all());
    }

    public function test_user_cannot_delete_other_account(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $account = $otherUser->accounts()->save(Account::factory()->make());
        $this->assertCount(1, $otherUser->accounts);

        $response = $this->actingAs($user)->delete(
            route(
                'accounts.destroy',
                ['account' => $account]
            )
        );

        $response->assertNotFound();
        $this->assertCount(1, $otherUser->fresh()->accounts);
    }

    public function test_guest_cannot_delete_any_account(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->save(Account::factory()->make());
        $this->assertCount(1, $user->accounts);

        $response = $this->delete(
            route(
                'accounts.destroy',
                ['account' => $account]
            )
        );

        $response->assertRedirectToRoute('login');
        $this->assertCount(1, $user->fresh()->accounts);
    }
}
