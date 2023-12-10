<?php

namespace Tests\Feature\Transaction;

use App\Enums\TransactionType;
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
            'type' => TransactionType::INCOME->value,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertCount(1, Transaction::all());
        tap(Transaction::first(), function (Transaction $transaction) use ($user) {
            $this->assertEquals($user->id, $transaction->user_id);
            $this->assertEquals(Carbon::parse('2023-01-01'), $transaction->date);
            $this->assertEquals(1000, $transaction->amount);
            $this->assertEquals('Test transaction', $transaction->description);
            $this->assertEquals(TransactionType::INCOME, $transaction->type);
        });
    }

    public function test_guest_cannot_create_transaction()
    {
        $response = $this->from('/dashboard')->post('/transactions', $this->validParams());

        $response->assertRedirectToRoute('login');
    }

    public function test_date_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            '/transactions',
            $this->validParams(['date' => ''])
        );

        $response->assertSessionHasErrors('date');
        $this->assertCount(0, Transaction::all());
    }

    public function test_date_must_be_correct_date(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            '/transactions',
            $this->validParams(['date' => 'not-date'])
        );

        $response->assertSessionHasErrors('date');
        $this->assertCount(0, Transaction::all());
    }

    public function test_amount_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            '/transactions',
            $this->validParams(['amount' => ''])
        );

        $response->assertSessionHasErrors('amount');
        $this->assertCount(0, Transaction::all());
    }

    public function test_amount_must_be_numeric(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            '/transactions',
            $this->validParams(['amount' => 'not-numeric'])
        );

        $response->assertSessionHasErrors('amount');
        $this->assertCount(0, Transaction::all());
    }

    public function test_amount_must_be_greater_than_0(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            '/transactions',
            $this->validParams(['amount' => '0'])
        );

        $response->assertSessionHasErrors('amount');
        $this->assertCount(0, Transaction::all());
    }

    public function test_description_is_optional(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            '/transactions',
            $this->validParams(['description' => ''])
        );

        $response->assertSessionHasNoErrors();
        $this->assertCount(1, Transaction::all());
    }

    public function test_type_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            '/transactions',
            $this->validParams(['type' => ''])
        );

        $response->assertSessionHasErrors('type');
        $this->assertCount(0, Transaction::all());
    }

    public function test_type_must_be_valid_type(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            '/transactions',
            $this->validParams(['type' => 'not-valid-type'])
        );

        $response->assertSessionHasErrors('type');
        $this->assertCount(0, Transaction::all());
    }

    private function validParams(array $overrides = []): array
    {
        return [
            'date' => '2023-01-01',
            'amount' => 1000,
            'description' => 'Test transaction',
            'type' => TransactionType::INCOME->value,
            ...$overrides,
        ];
    }
}
