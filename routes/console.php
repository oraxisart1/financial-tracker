<?php

use App\Contracts\CurrencyService;
use App\Models\Currency;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('import-currency', function (CurrencyService $currencyService) {
    $currencies = $currencyService->fetchCurrencyList();
    $existingCodes = Currency::all()->pluck('code')->toArray();

    $currenciesToCreate = $currencies
        ->filter(fn(array $currency) => !in_array($currency['code'], $existingCodes, true));
    $data = $currenciesToCreate
        ->map(fn(array $currency) => ['code' => $currency['code'], 'name' => $currency['name']])->toArray();
    Currency::insert($data);
});
