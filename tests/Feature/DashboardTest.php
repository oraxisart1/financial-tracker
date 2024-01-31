<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_dashboard_page(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(
            route(
                'dashboard',
                ['transaction_type' => TransactionType::EXPENSE->value]
            )
        );

        $response->assertStatus(200);
    }

    public function test_guest_cannot_see_dashboard_page()
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirectToRoute('login');
    }

    public function test_user_can_see_their_own_transactions()
    {
        $user = User::factory()->create();
        $user->transactions()->saveMany(Transaction::factory(3)->make(['type' => TransactionType::EXPENSE]));

        $response = $this->actingAs($user)->get(
            route(
                'dashboard',
                [
                    'transaction_type' => TransactionType::EXPENSE->value,
                    'all_time' => true,
                ]
            )
        );
        $response->assertInertia(fn(AssertableInertia $page) => $page
            ->component('Dashboard')
            ->has('transactions', 3));
    }

    public function test_user_cannot_see_other_transactions()
    {
        $user = User::factory()->create();
        $user->transactions()->saveMany(Transaction::factory(3)->make(['type' => TransactionType::EXPENSE]));

        $otherUser = User::factory()->create();
        $otherUser->transactions()->saveMany(Transaction::factory(3)->make(['type' => TransactionType::EXPENSE]));

        $response = $this->actingAs($user)->get(
            route(
                'dashboard',
                [
                    'transaction_type' => TransactionType::EXPENSE->value,
                    'all_time' => true,
                ]
            )
        );
        $response->assertInertia(fn(AssertableInertia $page) => $page
            ->component('Dashboard')
            ->has('transactions', 3));
    }

    public function test_user_see_only_filtered_by_type_incomes()
    {
        $user = User::factory()->create();
        $user->transactions()->saveMany(Transaction::factory(3)->make(['type' => TransactionType::INCOME->value]));
        $user->transactions()->saveMany(Transaction::factory(5)->make(['type' => TransactionType::EXPENSE->value]));

        $response = $this->actingAs($user)->get(
            route(
                'dashboard',
                [
                    'transaction_type' => TransactionType::INCOME->value,
                    'all_time' => true,
                ]
            )
        );
        $response->assertInertia(fn(AssertableInertia $page) => $page
            ->component('Dashboard')
            ->has('transactions', 3));
    }

    public function test_user_see_transactions_categories()
    {
        $user = User::factory()->create();

        $categoryA = Category::factory()->create(['type' => TransactionType::INCOME->value, 'user_id' => $user->id]);
        $categoryB = Category::factory()->create(['type' => TransactionType::INCOME->value, 'user_id' => $user->id]);
        $categoryC = Category::factory()->create(['type' => TransactionType::INCOME->value, 'user_id' => $user->id]);
        $user->transactions()->saveMany([
            Transaction::factory()->make([
                'type' => TransactionType::INCOME->value,
                'user_id' => $user->id,
                'category_id' => $categoryA->id,
            ]),
            Transaction::factory()->make([
                'type' => TransactionType::INCOME->value,
                'user_id' => $user->id,
                'category_id' => $categoryB->id,
            ]),
            Transaction::factory()->make([
                'type' => TransactionType::INCOME->value,
                'user_id' => $user->id,
                'category_id' => $categoryC->id,
            ]),
        ]);

        $response = $this->actingAs($user)->get(
            route(
                'dashboard',
                [
                    'transaction_type' => TransactionType::INCOME->value,
                    'all_time' => true,
                ]
            )
        );
        $response->assertInertia(fn(AssertableInertia $page) => $page
            ->component('Dashboard')
            ->has('categories', 3));
    }

    public function test_user_can_filter_transactions_by_category()
    {
        $user = User::factory()->create();

        $categoryA = Category::factory()->create(['type' => TransactionType::INCOME->value, 'user_id' => $user->id]);
        $categoryB = Category::factory()->create(['type' => TransactionType::INCOME->value, 'user_id' => $user->id]);
        $categoryC = Category::factory()->create(['type' => TransactionType::INCOME->value, 'user_id' => $user->id]);
        $user->transactions()->saveMany([
            Transaction::factory()->make([
                'type' => TransactionType::INCOME->value,
                'user_id' => $user->id,
                'category_id' => $categoryA->id,
            ]),
            Transaction::factory()->make([
                'type' => TransactionType::INCOME->value,
                'user_id' => $user->id,
                'category_id' => $categoryB->id,
            ]),
            Transaction::factory()->make([
                'type' => TransactionType::INCOME->value,
                'user_id' => $user->id,
                'category_id' => $categoryC->id,
            ]),
        ]);

        $response = $this->actingAs($user)->get(
            route(
                'dashboard',
                [
                    'transaction_type' => TransactionType::INCOME->value,
                    'all_time' => true,
                    'category' => $categoryA->id,
                ]
            )
        );

        $response->assertInertia(fn(AssertableInertia $page) => $page
            ->component('Dashboard')
            ->has('transactions', 1)
            ->has('categories', 3));
    }

    public function test_dashboard_page_recieves_user_accounts()
    {
        $user = User::factory()->create();
        $user->accounts()->saveMany(Account::factory(10)->make(['user_id' => $user->id]));

        $response = $this->actingAs($user)->get(
            route(
                'dashboard',
                [
                    'transaction_type' => TransactionType::INCOME->value,
                    'all_time' => true,
                ]
            )
        );

        $response->assertInertia(fn(AssertableInertia $page) => $page
            ->component('Dashboard')
            ->has('accounts', 10));
    }

    public function test_transaction_filtering_by_date()
    {
        $user = User::factory()->create();

        $user->transactions()->saveMany([
            Transaction::factory()->create([
                'date' => Carbon::parse(date('2024-01-01')),
                'user_id' => $user->id,
                'type' => TransactionType::INCOME,
            ]),
            Transaction::factory()->create([
                'date' => Carbon::parse(date('2023-01-01')),
                'user_id' => $user->id,
                'type' => TransactionType::INCOME,
            ]),
        ]);

        $response = $this->actingAs($user)->get(
            route(
                'dashboard',
                [
                    'transaction_type' => TransactionType::INCOME->value,
                    'date_from' => '2024-01-01',
                    'date_to' => '2024-01-31',
                ]
            )
        );

        $response->assertInertia(fn(AssertableInertia $page) => $page
            ->component('Dashboard')
            ->has('transactions', 1));
    }
}
