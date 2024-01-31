<?php

namespace Tests\Feature\Transaction;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CreateTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);
        $account = Account::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->from(route('dashboard'))->post(
            route('transactions.store'),
            [
                'date' => '2023-01-01',
                'amount' => 1000,
                'description' => 'Test transaction',
                'type' => TransactionType::INCOME->value,
                'currency' => 'USD',
                'category_id' => $category->id,
                'account_id' => $account->id,
            ]
        );

        $response->assertSessionHasNoErrors();
        $this->assertCount(1, Transaction::all());
        tap(Transaction::first(), function (Transaction $transaction) use ($user, $category, $account) {
            $this->assertTrue($transaction->user->is($user));
            $this->assertEquals($user->id, $transaction->user_id);
            $this->assertEquals(Carbon::parse('2023-01-01'), $transaction->date);
            $this->assertEquals(1000, $transaction->amount);
            $this->assertEquals('Test transaction', $transaction->description);
            $this->assertEquals(TransactionType::INCOME, $transaction->type);
            $this->assertTrue($transaction->currency->is(Currency::findByCode('USD')));
            $this->assertTrue($transaction->category->is($category));
            $this->assertTrue($transaction->account->is($account));
        });
    }

    public function test_transaction_actually_affects_accounts_balance()
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'balance' => 2000,
        ]);
        $this->assertEqualsWithDelta(2000, $account->balance, 0.0001);

        $this->actingAs($user)->post(
            route('transactions.store'),
            $this->validParams([
                'amount' => 1000,
                'type' => TransactionType::INCOME->value,
                'account_id' => $account->id,
            ])
        );
        $this->assertEqualsWithDelta(3000, $account->fresh()->balance, 0.0001);

        $this->actingAs($user)->post(
            route('transactions.store'),
            $this->validParams([
                'amount' => 2000,
                'type' => TransactionType::EXPENSE->value,
                'account_id' => $account->id,
            ])
        );
        $this->assertEqualsWithDelta(1000, $account->fresh()->balance, 0.0001);
    }

    public function test_user_cannot_create_transaction_with_other_user_account()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->post(
            route('transactions.store'),
            $this->validParams([
                'account_id' => $account->id,
            ])
        );

        $response->assertSessionHasErrors('account_id');
        $this->assertCount(0, Transaction::all());
    }

    public function test_user_cannot_create_transaction_with_other_user_category()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->post(
            route('transactions.store'),
            $this->validParams([
                'category_id' => $category->id,
            ])
        );

        $response->assertSessionHasErrors('category_id');
        $this->assertCount(0, Transaction::all());
    }

    public function test_guest_cannot_create_transaction()
    {
        $response = $this->from('/dashboard')->post(
            route('transactions.store'),
            $this->validParams()
        );

        $response->assertRedirectToRoute('login');
    }

    public function test_date_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            route('transactions.store'),
            $this->validParams(
                ['date' => '']
            )
        );

        $response->assertSessionHasErrors('date');
        $this->assertCount(0, Transaction::all());
    }

    public function test_date_must_be_correct_date(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            route('transactions.store'),
            $this->validParams(
                ['date' => 'not-date']
            )
        );

        $response->assertSessionHasErrors('date');
        $this->assertCount(0, Transaction::all());
    }

    public function test_amount_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            route('transactions.store'),
            $this->validParams(
                ['amount' => '']
            )
        );

        $response->assertSessionHasErrors('amount');
        $this->assertCount(0, Transaction::all());
    }

    public function test_amount_must_be_numeric(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            route('transactions.store'),
            $this->validParams(
                ['amount' => 'not-numeric']
            )
        );

        $response->assertSessionHasErrors('amount');
        $this->assertCount(0, Transaction::all());
    }

    public function test_amount_must_be_greater_than_0(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            route('transactions.store'),
            $this->validParams(
                ['amount' => '0']
            )
        );

        $response->assertSessionHasErrors('amount');
        $this->assertCount(0, Transaction::all());
    }

    public function test_description_is_optional(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            route('transactions.store'),
            $this->validParams(
                ['description' => '']
            )
        );

        $response->assertSessionHasNoErrors();
        $this->assertCount(1, Transaction::all());
    }

    public function test_type_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            route('transactions.store'),
            $this->validParams(
                ['type' => '']
            )
        );

        $response->assertSessionHasErrors('type');
        $this->assertCount(0, Transaction::all());
    }

    public function test_type_must_be_valid_type(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            route('transactions.store'),
            $this->validParams(
                ['type' => 'not-valid-type']
            )
        );

        $response->assertSessionHasErrors('type');
        $this->assertCount(0, Transaction::all());
    }

    public function test_currency_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            route('transactions.store'),
            $this->validParams(
                ['currency' => '']
            )
        );

        $response->assertSessionHasErrors('currency');
        $this->assertCount(0, Transaction::all());
    }

    public function test_currency_must_be_existing_currency(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            route('transactions.store'),
            $this->validParams(
                ['currency' => 'NOT-EXISTING-CURRENCY']
            )
        );

        $response->assertSessionHasErrors('currency');
        $this->assertCount(0, Transaction::all());
    }

    public function test_category_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            route('transactions.store'),
            $this->validParams(
                ['category_id' => '']
            )
        );

        $response->assertSessionHasErrors('category_id');
        $this->assertCount(0, Transaction::all());
    }

    public function test_category_must_be_existing_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            route('transactions.store'),
            $this->validParams(
                ['category_id' => '999']
            )
        );

        $response->assertSessionHasErrors('category_id');
        $this->assertCount(0, Transaction::all());
    }

    public function test_account_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            route('transactions.store'),
            $this->validParams(
                ['account_id' => '']
            )
        );

        $response->assertSessionHasErrors('account_id');
        $this->assertCount(0, Transaction::all());
    }

    public function test_account_must_be_existing_account(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            route('transactions.store'),
            $this->validParams(
                ['account_id' => '999']
            )
        );

        $response->assertSessionHasErrors('account_id');
        $this->assertCount(0, Transaction::all());
    }

    private function validParams(array $overrides = []): array
    {
        $categoryAttributes = [];
        $accountAttributes = [];
        if (Auth::user()) {
            $categoryAttributes['user_id'] = Auth::user()->id;
            $accountAttributes['user_id'] = Auth::user()->id;
        }

        return [
            'date' => '2023-01-01',
            'amount' => 1000,
            'description' => 'Test transaction',
            'type' => TransactionType::INCOME->value,
            'currency' => 'USD',
            'category_id' => Category::factory()->create($categoryAttributes)->id,
            'account_id' => Account::factory()->create($accountAttributes)->id,
            ...$overrides,
        ];
    }
}
