<?php

namespace Tests\Unit\Models;

use App\Enums\CategoryType;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_type_scope_returns_filtered_transactions(): void
    {
        $incomeCategory = Category::factory()->create(['type' => CategoryType::INCOME]);
        $expenseCategory = Category::factory()->create(['type' => CategoryType::EXPENSE]);
        $incomeCategory->transactions()->saveMany(Transaction::factory(3)->create());
        $expenseCategory->transactions()->saveMany(Transaction::factory(5)->create());

        self::assertCount(3, Transaction::ofType(CategoryType::INCOME)->get());
        self::assertCount(5, Transaction::ofType(CategoryType::EXPENSE)->get());
    }
}
