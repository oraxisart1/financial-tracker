<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\DTO\TransactionDTO;
use App\Enums\CategoryType;
use App\Models\Account;
use App\Models\AccountTransfer;
use App\Models\Category;
use App\Models\Currency;
use App\Models\User;
use App\Services\AccountTransferService;
use App\Services\TransactionService;
use Carbon\Carbon;
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
            Account::factory(20)->create([
                'user_id' => $user->id,
                'currency_id' => fn() => Currency::findByCode(fake()->currencyCode())->id,
            ])
        );

        $user->categories()->saveMany(
            Category::factory(20)->create([
                'user_id' => $user->id,
                'type' => CategoryType::EXPENSE,
            ])
        );

        $user->categories()->saveMany(
            Category::factory(20)->create([
                'user_id' => $user->id,
                'type' => CategoryType::INCOME,
            ])
        );

        foreach (Category::all() as $category) {
            for ($i = 0; $i < 20; $i++) {
                $account = $user->accounts()->inRandomOrder()->first();
                app(TransactionService::class)->createTransaction(
                    new TransactionDTO(
                        Carbon::parse(fake()->dateTimeThisYear()),
                        fake()->randomFloat(2, 0, 1000),
                        fake()->currencyCode(),
                        $category->id,
                        $account->id,
                        $user->id,
                        fake()->text(50)
                    )
                );
            }
        }

        for ($i = 0; $i < 100; $i++) {
            $accountFrom = $user->accounts()->inRandomOrder()->first();
            $accountTo = $user->accounts()->whereNot('id', $accountFrom->id)->inRandomOrder()->first();

            app(AccountTransferService::class)->transferBetweenAccounts(
                $accountFrom,
                $accountTo,
                AccountTransfer::factory()->make([
                    'user_id' => $user->id,
                ])
            );
        }
    }
}
