<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
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
        $response = $this->actingAs($user)->get('/dashboard');

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
        $user->transactions()->saveMany(Transaction::factory(3)->make());

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertInertia(fn(AssertableInertia $page) => $page
            ->component('Dashboard')
            ->has('transactions', 3));
    }

    public function test_user_cannot_see_other_transactions()
    {
        $user = User::factory()->create();
        $user->transactions()->saveMany(Transaction::factory(3)->make());

        $otherUser = User::factory()->create();
        $otherUser->transactions()->saveMany(Transaction::factory(3)->make());

        $response = $this->actingAs($user)->get('/dashboard');
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
}
