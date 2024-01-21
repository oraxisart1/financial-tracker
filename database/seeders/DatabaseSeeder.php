<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Enums\CategoryType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Artisan::call('import-currency');

        $user = User::factory()->create([
            'name' => 'Example Account',
            'email' => 'account@example.com',
            'password' => bcrypt('password'),
        ]);

        $user->accounts()->saveMany(
            Account::factory(10)->create([
                'user_id' => $user->id,
                'currency_id' => fn() => Currency::findByCode(fake()->currencyCode())->id,
            ])
        );

        $user->categories()->saveMany(
            Category::factory(10)->create([
                'user_id' => $user->id,
                'type' => CategoryType::EXPENSE,
            ])
        );

        $user->categories()->saveMany(
            Category::factory(10)->create([
                'user_id' => $user->id,
                'type' => CategoryType::INCOME,
            ])
        );

        foreach (Category::all() as $category) {
            $category->transactions()->saveMany(
                Transaction::factory(10)->create([
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'account_id' => $user->accounts()->inRandomOrder()->first()->id,
                    'type' => $category->type,
                    'currency_id' => Currency::findByCode(fake()->currencyCode())->id,
                ])
            );
        }
    }
}
