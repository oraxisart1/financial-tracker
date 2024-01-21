<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->text(10),
            'currency_id' => Currency::findByCode('USD')->id,
            'balance' => fake()->randomFloat(),
            'color' => fake()->hexColor(),
            'user_id' => fn() => User::factory()->create()->id,
        ];
    }
}
