<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_toggle_state_of_their_account()
    {
        $user = User::factory()->create();
        $account = $user->accounts()->save(
            Account::factory()->create([
                'active' => false,
            ])
        );

        $this->actingAs($user)->post(
            route(
                'accounts.toggle',
                ['account' => $account]
            )
        );
        $this->assertEquals(true, $account->fresh()->active);

        $this->actingAs($user)->post(
            route(
                'accounts.toggle',
                ['account' => $account]
            )
        );
        $this->assertEquals(false, $account->fresh()->active);
    }

    public function test_user_cannot_toggle_state_of_other_account()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $account = $otherUser->accounts()->save(
            Account::factory()->create([
                'active' => false,
            ])
        );

        $response = $this->actingAs($user)->post(
            route(
                'accounts.toggle',
                ['account' => $account]
            )
        );

        $response->assertNotFound();
        $this->assertEquals(false, $account->fresh()->active);
    }

    public function test_guest_cannot_toggle_state_of_any_account()
    {
        $user = User::factory()->create();
        $account = $user->accounts()->save(
            Account::factory()->create([
                'active' => false,
            ])
        );

        $response = $this->post(
            route(
                'accounts.toggle',
                ['account' => $account]
            )
        );

        $response->assertRedirectToRoute('login');
        $this->assertEquals(false, $account->fresh()->active);
    }
}
