<?php

namespace Tests\Feature\AccountTransfer;

use App\Models\AccountTransfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ShowAccountTransferListTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_their_own_transfers(): void
    {
        $user = User::factory()->create();
        $user->accountTransfers()->saveMany(AccountTransfer::factory(3)->create());

        $response = $this->actingAs($user)->get(route('accounts.index'));

        $response->assertInertia(fn(AssertableInertia $page) => $page
            ->has('transfers', 3));
    }

    public function test_user_cannot_see_other_transfers(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $user->accountTransfers()->saveMany(AccountTransfer::factory(3)->create());
        $otherUser->accountTransfers()->saveMany(AccountTransfer::factory(3)->create());

        $response = $this->actingAs($user)->get(route('accounts.index'));

        $response->assertInertia(fn(AssertableInertia $page) => $page
            ->has('transfers', 3));
    }
}
