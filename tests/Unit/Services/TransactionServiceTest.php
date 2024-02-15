<?php

namespace Tests\Unit\Services;

use App\DTO\TransactionDTO;
use App\Enums\CategoryType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_transaction(): void
    {
        $transactionService = app(TransactionService::class);

        $category = Category::factory()->create();
        $account = Account::factory()->create();
        $user = User::factory()->create();
        $transaction = $transactionService->createTransaction(
            new TransactionDTO(
                Carbon::parse('2024-01-01'),
                1000,
                'USD',
                $category->id,
                $account->id,
                $user->id,
                'Test description'
            )
        );

        tap($transaction->fresh(), function (Transaction $transaction) use ($account, $category, $user) {
            $this->assertEquals(Carbon::parse('2024-01-01'), $transaction->date);
            $this->assertEqualsWithDelta(1000, $transaction->amount, 0);
            $this->assertTrue($transaction->currency->is(Currency::findByCode('USD')));
            $this->assertTrue($transaction->account->is($account));
            $this->assertTrue($transaction->category->is($category));
            $this->assertTrue($transaction->user->is($user));
            $this->assertEquals('Test description', $transaction->description);
        });
    }

    public function test_creating_transaction_changes_account_balance()
    {
        $transactionService = app(TransactionService::class);

        $account = Account::factory()->create([
            'balance' => 1000,
        ]);
        $this->assertEqualsWithDelta(1000, $account->balance, 0);

        $transactionService->createTransaction(
            new TransactionDTO(
                Carbon::parse('2024-01-01'),
                1000,
                'USD',
                Category::factory()->create(['type' => CategoryType::INCOME])->id,
                $account->id,
                User::factory()->create()->id,
                'Test description'
            )
        );
        $this->assertEqualsWithDelta(2000, $account->fresh()->balance, 0);

        $transactionService->createTransaction(
            new TransactionDTO(
                Carbon::parse('2024-01-01'),
                500,
                'USD',
                Category::factory()->create(['type' => CategoryType::EXPENSE])->id,
                $account->id,
                User::factory()->create()->id,
                'Test description'
            )
        );
        $this->assertEqualsWithDelta(1500, $account->fresh()->balance, 0);
    }
}
