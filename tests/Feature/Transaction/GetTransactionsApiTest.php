<?php

namespace Feature\Transaction;

use App\Enums\CategoryType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GetTransactionsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_transaction_list(): void
    {
        $user = User::factory()->create();
        $user->transactions()->saveMany(
            Transaction::factory(2)->create()
        );

        Sanctum::actingAs($user);

        $response = $this->getJson(
            route(
                'api.transactions.index',
                ['per_page' => 3]
            )
        );

        $response->assertOk();
        $this->assertCount(2, $response->json('transactions'));
    }

    public function test_user_can_get_only_their_transactions(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $transaction = $user->transactions()->save(
            Transaction::factory()->create()
        );
        $otherUser->transactions()->save(
            Transaction::factory()->create()
        );

        Sanctum::actingAs($user);
        $response = $this->getJson(
            route(
                'api.transactions.index',
                [
                    'per_page' => 2,
                ]
            )
        );

        $this->assertTrue($transaction->is(Transaction::find($response->json('transactions')[0]['id'])));
    }

    public function test_guest_cannot_get_any_transactions(): void
    {
        $response = $this->getJson(route('api.transactions.index'));

        $response->assertUnauthorized();
    }

    public function test_paginating_transactions()
    {
        $user = User::factory()->create();
        $transactionA = $user->transactions()->save(
            Transaction::factory()->create()
        );
        $transactionB = $user->transactions()->save(
            Transaction::factory()->create()
        );

        Sanctum::actingAs($user);

        $response = $this->getJson(
            route(
                'api.transactions.index',
                [
                    'page' => 1,
                    'per_page' => 1,
                ]
            )
        );

        $idA = $response->json('transactions')[0]['id'];
        $this->assertCount(1, $response->json('transactions'));

        $response = $this->getJson(
            route(
                'api.transactions.index',
                [
                    'page' => 2,
                    'per_page' => 1,
                ]
            )
        );

        $idB = $response->json('transactions')[0]['id'];
        $this->assertCount(1, $response->json('transactions'));
        $this->assertNotEquals($idA, $idB);
    }

    public function test_filtering_by_transaction_type()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $incomeCategory = $user->categories()->save(
            Category::factory()->create(['type' => CategoryType::INCOME])
        );
        $transactionA = $incomeCategory->transactions()->save(
            Transaction::factory()->create(['user_id' => $user->id])
        );
        $expenseCategory = $user->categories()->save(
            Category::factory()->create(['type' => CategoryType::EXPENSE])
        );
        $transactionB = $expenseCategory->transactions()->save(
            Transaction::factory()->create(['user_id' => $user->id])
        );

        $response = $this->getJson(
            route(
                'api.transactions.index',
                [
                    'per_page' => 2,
                    'type' => CategoryType::INCOME->value,
                ]
            )
        );
        $this->assertTrue($transactionA->is(Transaction::find($response->json('transactions')[0]['id'])));
        $this->assertCount(1, $response->json('transactions'));
    }

    public function test_filtering_by_category_id()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $categoryA = $user->categories()->save(
            Account::factory()->create()
        );
        $categoryB = $user->categories()->save(
            Account::factory()->create()
        );

        $transactionA = $user->transactions()->save(
            Transaction::factory()->create(['category_id' => $categoryA->id])
        );
        $transactionB = $user->transactions()->save(
            Transaction::factory()->create(['category_id' => $categoryB->id])
        );

        $response = $this->getJson(
            route(
                'api.transactions.index',
                [
                    'per_page' => 2,
                    'category_id' => $categoryA->id,
                ]
            )
        );
        $this->assertTrue($transactionA->is(Transaction::find($response->json('transactions')[0]['id'])));
        $this->assertCount(1, $response->json('transactions'));
    }

    public function test_filtering_by_date_from()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $transactionA = $user->transactions()->save(
            Transaction::factory()->create(['date' => Carbon::parse('now')])
        );
        $transactionB = $user->transactions()->save(
            Transaction::factory()->create(['date' => Carbon::parse('1 day ago')])
        );

        $response = $this->getJson(
            route(
                'api.transactions.index',
                [
                    'per_page' => 2,
                    'date_from' => Carbon::parse('now')->format('Y-m-d'),
                ]
            )
        );
        $this->assertTrue($transactionA->is(Transaction::find($response->json('transactions')[0]['id'])));
        $this->assertCount(1, $response->json('transactions'));
    }

    public function test_filtering_by_date_to()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $transactionA = $user->transactions()->save(
            Transaction::factory()->create(['date' => Carbon::parse('now')])
        );
        $transactionB = $user->transactions()->save(
            Transaction::factory()->create(['date' => Carbon::parse('+1 day')])
        );

        $response = $this->getJson(
            route(
                'api.transactions.index',
                [
                    'per_page' => 2,
                    'date_to' => Carbon::parse('now')->format('Y-m-d'),
                ]
            )
        );
        $this->assertTrue($transactionA->is(Transaction::find($response->json('transactions')[0]['id'])));
        $this->assertCount(1, $response->json('transactions'));
    }

    public function test_correct_ordering()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $transactionsA = $user->transactions()->save(
            Transaction::factory()->create(['date' => Carbon::parse('2 days ago')])
        );
        $transactionsB = $user->transactions()->save(
            Transaction::factory()->create(['date' => Carbon::parse('now')])
        );
        $transactionsC = $user->transactions()->save(
            Transaction::factory()->create(['date' => Carbon::parse('1 day ago')])
        );
        $transactionsD = $user->transactions()->save(
            Transaction::factory()->create(['date' => Carbon::parse('now')])
        );

        $order = collect([
            $transactionsD,
            $transactionsB,
            $transactionsC,
            $transactionsA,
        ]);

        $response = $this->getJson(
            route(
                'api.transactions.index',
                [
                    'per_page' => 4,
                ]
            )
        );

        $this->assertEquals(
            $order->pluck('id'),
            collect($response->json('transactions'))->pluck('id')
        );
    }
}
