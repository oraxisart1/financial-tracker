<?php

namespace Tests\Unit\Models;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_transaction_to_account(): void
    {
        $account = Account::factory()->create();
        $this->assertCount(0, $account->transactions);

        $transaction = Transaction::factory()->create();
        $account->addTransaction($transaction);
        tap($account->fresh(), function (Account $account) use ($transaction) {
            $this->assertTrue($account->transactions()->first()->is($transaction));
            $this->assertCount(1, $account->transactions);
        });
    }

    public function test_adding_transaction_affects_account_balance()
    {
        $account = Account::factory()->create([
            'balance' => 0,
        ]);

        $account->addTransaction(
            Transaction::factory()->create([
                'amount' => 1000,
                'type' => TransactionType::INCOME,
            ])
        );

        $this->assertEqualsWithDelta(1000, $account->balance, 0);
    }

    public function test_toggle_state()
    {
        $account = Account::factory()->create([
            'active' => true,
        ]);
        $this->assertEquals(true, $account->fresh()->active);

        $account->toggleState();

        $this->assertEquals(false, $account->fresh()->active);

        $account->toggleState();

        $this->assertEquals(true, $account->fresh()->active);
    }

    public function test_add_balance()
    {
        $account = Account::factory()->create([
            'balance' => 0,
        ]);
        $this->assertEqualsWithDelta(0, $account->balance, 0);

        $account->addBalance(1000);
        $this->assertEqualsWithDelta(1000, $account->fresh()->balance, 0);

        $account->addBalance(-500);
        $this->assertEqualsWithDelta(500, $account->fresh()->balance, 0);
    }
}
