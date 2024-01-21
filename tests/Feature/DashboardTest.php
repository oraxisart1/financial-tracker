<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_dashboard_page(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/dashboard?transaction_type=expense');

        $response->assertStatus(200);
    }

    public function test_guest_cannot_see_dashboard_page()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirectToRoute('login');
    }

    public function test_user_can_see_their_own_transactions()
    {
        $user = User::factory()->create();
        $user->transactions()->saveMany(Transaction::factory(3)->make(['type' => TransactionType::EXPENSE]));

        $response = $this->actingAs($user)->get('/dashboard?transaction_type=expense');
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

        $response = $this->actingAs($user)->get('/dashboard?transaction_type=expense');
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
            '/dashboard?' . http_build_query(['transaction_type' => TransactionType::INCOME->value])
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
            '/dashboard?' . http_build_query(['transaction_type' => TransactionType::INCOME->value])
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
            sprintf(
                '%s?%s',
                '/dashboard',
                http_build_query([
                    'transaction_type' => TransactionType::INCOME->value,
                    'category' => $categoryA->id,
                ])
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
            sprintf(
                '%s?%s',
                '/dashboard',
                http_build_query([
                    'transaction_type' => TransactionType::INCOME->value,
                ])
            )
        );

        $response->assertInertia(fn(AssertableInertia $page) => $page
            ->component('Dashboard')
            ->has('accounts', 10));
    }
}
