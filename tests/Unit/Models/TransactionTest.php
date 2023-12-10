<?php

namespace Tests\Unit\Models;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_type_scope_returns_filtered_transactions(): void
    {
        $incomes = Transaction::factory(3)->create(['type' => TransactionType::INCOME]);
        $expenses = Transaction::factory(5)->create(['type' => TransactionType::EXPENSE]);

        self::assertCount(3, Transaction::ofType(TransactionType::INCOME)->get());
    }
}
