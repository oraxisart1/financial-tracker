<?php

namespace App\Providers;

use App\Contracts\CurrencyService;
use App\Services\ApiCurrencyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CurrencyService::class, ApiCurrencyService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::$wrap = false;

        Request::macro(
            'perPage',
            fn() => $this->get('per_page', config('app.pagination_size'))
        );
    }
}
