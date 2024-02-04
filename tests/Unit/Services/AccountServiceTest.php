<?php

namespace Tests\Unit\Services;

use App\Models\Account;
use App\Models\AccountTransfer;
use App\Models\Transaction;
use App\Services\AccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_account(): void
    {
        $account = Account::factory()->create();
        $this->assertCount(1, Account::all());

        (new AccountService())->delete($account, AccountService::DELETE_CASCADE_MODE);

        $this->assertCount(0, Account::all());
    }

    public function test_deleting_account_with_cascade_removes_account_completely(): void
    {
        $account = Account::factory()->create();
        $this->assertCount(1, Account::withTrashed()->get());

        (new AccountService())->delete($account, AccountService::DELETE_CASCADE_MODE);

        $this->assertCount(0, Account::withTrashed()->get());
    }

    public function test_deleting_account_without_cascade_removes_account_softly(): void
    {
        $account = Account::factory()->create();
        $this->assertCount(0, Account::onlyTrashed()->get());

        (new AccountService())->delete($account, AccountService::DELETE_ACCOUNT_ONLY_MODE);

        $this->assertCount(1, Account::withTrashed()->get());
    }

    public function test_deleting_account_with_cascade_deletes_transactions_and_transfers(): void
    {
        $account = Account::factory()->create();
        $account->transactions()->saveMany(Transaction::factory(5)->create());
        $account->transfersFrom()->saveMany(AccountTransfer::factory(5)->create());
        $account->transfersTo()->saveMany(AccountTransfer::factory(5)->create());
        $this->assertCount(5, Transaction::all());
        $this->assertCount(10, AccountTransfer::all());

        (new AccountService())->delete($account, AccountService::DELETE_CASCADE_MODE);

        $this->assertCount(0, Transaction::all());
        $this->assertCount(0, AccountTransfer::all());
    }

    public function test_deleting_account_without_cascade_does_not_delete_transactions_and_transfers(): void
    {
        $account = Account::factory()->create();
        $account->transactions()->saveMany(Transaction::factory(5)->create());
        $account->transfersFrom()->saveMany(AccountTransfer::factory(5)->create());
        $account->transfersTo()->saveMany(AccountTransfer::factory(5)->create());
        $this->assertCount(5, Transaction::all());
        $this->assertCount(10, AccountTransfer::all());

        (new AccountService())->delete($account, AccountService::DELETE_ACCOUNT_ONLY_MODE);

        $this->assertCount(5, Transaction::all());
        $this->assertCount(10, AccountTransfer::all());
    }
}
