<?php

namespace Tests\Feature\Transaction;

use App\Enums\TransactionType;
use App\Models\Account;
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

        $response = $this->actingAs($user)->from(route('dashboard'))->delete(
            route(
                'transactions.destroy',
                ['transaction' => $transaction]
            )
        );

        $response->assertRedirectToRoute('dashboard');
        $this->assertEquals(0, Transaction::count());
    }

    public function test_deleting_transaction_affects_account_balance(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->save(
            Account::factory()->make([
                'balance' => 0,
            ])
        );
        $transaction = Transaction::factory()->make([
            'user_id' => $user->id,
            'amount' => 1000,
            'type' => TransactionType::INCOME,
        ]);
        $account->addTransaction($transaction);
        $this->assertEquals(1000, $account->fresh()->balance);

        $this->actingAs($user)->delete(
            route(
                'transactions.destroy',
                ['transaction' => $transaction]
            )
        );

        $this->assertEquals(0, $account->fresh()->balance);
    }

    public function test_user_cannot_delete_other_transaction(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->delete(
            route(
                'transactions.destroy',
                ['transaction' => $transaction]
            )
        );

        $response->assertStatus(404);
        $this->assertEquals(1, Transaction::count());
    }

    public function test_guest_cannot_delete_any_transaction(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->delete(
            route(
                'transactions.destroy',
                ['transaction' => $transaction]
            )
        );

        $response->assertRedirectToRoute('login');
        $this->assertEquals(1, Transaction::count());
    }
}
