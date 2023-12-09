<?php

namespace Tests\Feature\Transaction;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_their_own_transaction(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete("/transactions/$transaction->id");

        $response->assertRedirect('/dashboard/');
        $this->assertEquals(0, Transaction::count());
    }

    public function test_user_cannot_delete_other_transaction(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->delete("/transactions/$transaction->id");

        $response->assertStatus(404);
        $this->assertEquals(1, Transaction::count());
    }

    public function test_guest_cannot_delete_any_transaction(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->delete("/transactions/$transaction->id");

        $response->assertRedirectToRoute('login');
        $this->assertEquals(1, Transaction::count());
    }
}
