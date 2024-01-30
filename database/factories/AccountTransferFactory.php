<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AccountTransfer;
use App\Models\Currency;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccountTransfer>
 */
class AccountTransferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fn() => User::factory()->create()->id,
            'account_from_id' => fn() => Account::factory([
                'currency_id' => Currency::findByCode('USD'),
            ])->create()->id,
            'account_to_id' => fn() => Account::factory([
                'currency_id' => Currency::findByCode('USD'),
            ])->create()->id,
            'amount' => 1000,
            'date' => Carbon::parse('2024-01-01'),
        ];
    }
}
