<?php

namespace Database\Factories;

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
            'amount' => 1000.0000,
            'description' => 'Test transaction',
            'date' => Carbon::parse('-3 days'),
            'user_id' => fn() => User::factory()->create()->id,
        ];
    }
}
