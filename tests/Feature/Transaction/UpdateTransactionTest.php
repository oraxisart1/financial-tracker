<?php

namespace Tests\Feature\Transaction;

use App\Enums\TransactionType;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_their_own_transaction(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'amount' => 1000,
            'description' => 'Old description',
            'date' => Carbon::parse('2023-11-11'),
            'type' => TransactionType::INCOME,
            'currency_id' => Currency::findByCode('USD'),
        ]);

        $response = $this->actingAs($user)->patch("/transactions/$transaction->id", [
            'amount' => '2000',
            'description' => 'New description',
            'date' => '2024-11-11',
            'currency' => 'EUR',
        ]);

        $response->assertSessionHasNoErrors();
        tap($transaction->fresh(), function (Transaction $transaction) {
            $this->assertEqualsWithDelta(2000, $transaction->amount, 0.0001);
            $this->assertEquals('New description', $transaction->description);
            $this->assertEquals(Carbon::parse('2024-11-11'), $transaction->date);
            $this->assertTrue($transaction->currency->is(Currency::findByCode('EUR')));
        });
    }

    public function test_user_cannot_update_other_transaction(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'amount' => 1000,
            'description' => 'Old description',
            'date' => Carbon::parse('2023-11-11'),
        ]);

        $response = $this->actingAs($user)->patch("/transactions/$transaction->id", [
            'amount' => '2000',
            'description' => 'New description',
            'date' => '2024-11-11',
        ]);

        $response->assertStatus(404);
        tap($transaction->fresh(), function (Transaction $transaction) {
            $this->assertEqualsWithDelta(1000, $transaction->amount, 0.0001);
            $this->assertEquals('Old description', $transaction->description);
            $this->assertEquals(Carbon::parse('2023-11-11'), $transaction->date);
        });
    }

    public function test_guest_cannot_update_any_transaction(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'amount' => 1000,
            'description' => 'Old description',
            'date' => Carbon::parse('2023-11-11'),
        ]);

        $response = $this->patch("/transactions/$transaction->id", [
            'amount' => '2000',
            'description' => 'New description',
            'date' => '2024-11-11',
        ]);

        $response->assertRedirectToRoute('login');
        tap($transaction->fresh(), function (Transaction $transaction) {
            $this->assertEqualsWithDelta(1000, $transaction->amount, 0.0001);
            $this->assertEquals('Old description', $transaction->description);
            $this->assertEquals(Carbon::parse('2023-11-11'), $transaction->date);
        });
    }

    public function test_date_is_required(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2023-11-11'),
        ]);

        $response = $this->actingAs($user)->patch("/transactions/$transaction->id", $this->validParams(['date' => '']));

        $response->assertSessionHasErrors('date');
        tap($transaction->fresh(), function (Transaction $transaction) {
            $this->assertEquals(Carbon::parse('2023-11-11'), $transaction->date);
        });
    }

    public function test_date_must_be_valid_date(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2023-11-11'),
        ]);

        $response = $this->actingAs($user)->patch(
            "/transactions/$transaction->id",
            $this->validParams(['date' => 'not-date'])
        );

        $response->assertSessionHasErrors('date');
        tap($transaction->fresh(), function (Transaction $transaction) {
            $this->assertEquals(Carbon::parse('2023-11-11'), $transaction->date);
        });
    }

    public function test_amount_is_required(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'amount' => 1000,
        ]);

        $response = $this->actingAs($user)->patch(
            "/transactions/$transaction->id",
            $this->validParams(['amount' => ''])
        );

        $response->assertSessionHasErrors('amount');
        tap($transaction->fresh(), function (Transaction $transaction) {
            $this->assertEqualsWithDelta(1000, $transaction->amount, 0.0001);
        });
    }

    public function test_amount_must_be_numeric(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'amount' => 1000,
        ]);

        $response = $this->actingAs($user)->patch(
            "/transactions/$transaction->id",
            $this->validParams(['amount' => 'not-numeric'])
        );

        $response->assertSessionHasErrors('amount');
        tap($transaction->fresh(), function (Transaction $transaction) {
            $this->assertEqualsWithDelta(1000, $transaction->amount, 0.0001);
        });
    }

    public function test_amount_must_be_greater_than_0(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'amount' => 1000,
        ]);

        $response = $this->actingAs($user)->patch(
            "/transactions/$transaction->id",
            $this->validParams(['amount' => '0'])
        );

        $response->assertSessionHasErrors('amount');
        tap($transaction->fresh(), function (Transaction $transaction) {
            $this->assertEqualsWithDelta(1000, $transaction->amount, 0.0001);
        });
    }

    public function description_is_optional(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'description' => 'Old description',
        ]);

        $response = $this->actingAs($user)->patch(
            "/transactions/$transaction->id",
            $this->validParams(['description' => ''])
        );

        $response->assertSessionHasNoErrors();
        tap($transaction->fresh(), function (Transaction $transaction) {
            $this->assertNull($transaction->description);
        });
    }

    public function test_currency_is_required(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'currency_id' => Currency::findByCode('USD'),
        ]);

        $response = $this->actingAs($user)->patch(
            "/transactions/$transaction->id",
            $this->validParams(['currency' => ''])
        );

        $response->assertSessionHasErrors('currency');
        tap($transaction->fresh(), function (Transaction $transaction) {
            $this->assertTrue($transaction->currency->is(Currency::findByCode('USD')));
        });
    }

    public function test_currency_must_be_existing_currency(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'currency_id' => Currency::findByCode('USD'),
        ]);

        $response = $this->actingAs($user)->patch(
            "/transactions/$transaction->id",
            $this->validParams(['currency' => 'NOT-EXISTING-CURRENCY'])
        );

        $response->assertSessionHasErrors('currency');
        tap($transaction->fresh(), function (Transaction $transaction) {
            $this->assertTrue($transaction->currency->is(Currency::findByCode('USD')));
        });
    }

    private function validParams(array $overrides = []): array
    {
        return [
            'amount' => '2000',
            'description' => 'New description',
            'date' => '2024-11-11',
            ...$overrides,
        ];
    }
}
