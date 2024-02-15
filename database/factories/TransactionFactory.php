<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => fake()->randomFloat(2, 0, 1000),
            'description' => fake()->text(),
            'date' => Carbon::parse(fake()->dateTimeThisYear()),
            'user_id' => fn() => User::factory()->create()->id,
            'currency_id' => fn() => Currency::findByCode('USD')->id,
            'category_id' => fn() => Category::factory()->create()->id,
            'account_id' => fn() => Account::factory()->create()->id,
        ];
    }
}
