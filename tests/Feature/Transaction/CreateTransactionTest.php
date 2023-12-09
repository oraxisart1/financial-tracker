<?php

namespace Tests\Feature\Transaction;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_transaction(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post('/transactions', [
            'date' => '2023-01-01',
            'amount' => 1000,
            'description' => 'Test transaction',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertCount(1, Transaction::all());
        tap(Transaction::first(), function (Transaction $transaction) use ($user) {
            $this->assertEquals($user->id, $transaction->user_id);
            $this->assertEquals(Carbon::parse('2023-01-01'), $transaction->date);
            $this->assertEquals(1000, $transaction->amount);
            $this->assertEquals('Test transaction', $transaction->description);
        });
    }

    public function test_guest_cannot_create_transaction()
    {
        $response = $this->from('/dashboard')->post('/transactions', [
            'date' => '2023-01-01',
            'amount' => 1000,
            'description' => 'Test transaction',
        ]);

        $response->assertRedirectToRoute('login');
    }

    public function test_date_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post('/transactions', [
            'amount' => 1000,
            'description' => 'Test transaction',
            'date' => '',
        ]);

        $response->assertSessionHasErrors('date');
        $this->assertCount(0, Transaction::all());
    }

    public function test_date_must_be_correct_date(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post('/transactions', [
            'amount' => 1000,
            'description' => 'Test transaction',
            'date' => 'not-date',
        ]);

        $response->assertSessionHasErrors('date');
        $this->assertCount(0, Transaction::all());
    }

    public function test_amount_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post('/transactions', [
            'date' => '2023-01-01',
            'description' => 'Test transaction',
            'amount' => '',
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertCount(0, Transaction::all());
    }

    public function test_amount_must_be_numeric(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post('/transactions', [
            'date' => '2023-01-01',
            'description' => 'Test transaction',
            'amount' => 'not-numeric',
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertCount(0, Transaction::all());
    }

    public function test_amount_must_be_greater_than_0(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post('/transactions', [
            'date' => '2023-01-01',
            'description' => 'Test transaction',
            'amount' => '0',
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertCount(0, Transaction::all());
    }

    public function test_description_is_optional(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post('/transactions', [
            'date' => '2023-01-01',
            'amount' => 1000,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertCount(1, Transaction::all());
    }
}
