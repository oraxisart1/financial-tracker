<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ShowAccountListTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_their_own_accounts(): void
    {
        $user = User::factory()->create();
        $user->accounts()->saveMany(Account::factory(3)->create());
        $response = $this->actingAs($user)->get(route('accounts.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn(AssertableInertia $page) => $page
            ->component('Accounts')
            ->has('accounts', 3));
    }

    public function test_user_cannot_see_other_accounts(): void
    {
        $user = User::factory()->create();
        $user->accounts()->saveMany(Account::factory(3)->create());
        $otherUser = User::factory()->create();
        $otherUser->accounts()->saveMany(Account::factory(3)->create());
        $response = $this->actingAs($user)->get(route('accounts.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn(AssertableInertia $page) => $page
            ->component('Accounts')
            ->has('accounts', 3));
    }

    public function test_guest_cannot_see_any_accounts(): void
    {
        $user = User::factory()->create();
        $user->accounts()->saveMany(Account::factory(3)->create());
        $response = $this->get(route('accounts.index'));

        $response->assertRedirectToRoute('login');
    }
}
