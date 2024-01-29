<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\AccountTransfersController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionsController;
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')
        ->name('dashboard');

    Route::post('/transactions', [TransactionsController::class, 'store'])
        ->name('transactions.store');
    Route::patch('/transactions/{transaction}', [TransactionsController::class, 'update'])
        ->name('transactions.update')
        ->middleware('can:update,transaction');
    Route::delete('/transactions/{transaction}', [TransactionsController::class, 'destroy'])
        ->name('transactions.destroy')
        ->middleware('can:destroy,transaction');

    Route::post('/categories', [CategoriesController::class, 'store'])
        ->name('categories.store');
    Route::patch('/categories/{category}', [CategoriesController::class, 'update'])
        ->middleware('can:update,category')
        ->name('categories.update');

    Route::get('/accounts', [AccountsController::class, 'index'])
        ->name('accounts.index');
    Route::post('/accounts', [AccountsController::class, 'store'])
        ->name('accounts.store');
    Route::patch('/accounts/{account}', [AccountsController::class, 'update'])
        ->name('accounts.update')
        ->middleware('can:update,account');

    Route::post('/account-transfers', [AccountTransfersController::class, 'store'])
        ->name('account-transfers.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__ . '/auth.php';
