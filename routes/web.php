<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountTransferController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserSettingsController;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect(RouteServiceProvider::HOME);
});


Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::resource('transactions', TransactionController::class)
        ->only([
            'store',
            'update',
            'destroy',
        ]);

    Route::resource('categories', CategoryController::class)
        ->only([
            'store',
            'update',
            'destroy',
        ]);

    Route::post('/accounts/{account}/toggle', [AccountController::class, 'toggle'])
        ->name('accounts.toggle')
        ->middleware('can:toggle,account');
    Route::delete('/accounts/{account}/{mode?}', [AccountController::class, 'destroy'])
        ->name('accounts.destroy')
        ->middleware('can:delete,account');
    Route::resource('accounts', AccountController::class)
        ->only([
            'index',
            'store',
            'update',
        ]);

    Route::resource('account-transfers', AccountTransferController::class)
        ->only([
            'store',
            'update',
            'destroy',
        ]);

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::patch('/user-settings', [UserSettingsController::class, 'update'])
        ->name('user-settings.update');
});

require __DIR__ . '/auth.php';
