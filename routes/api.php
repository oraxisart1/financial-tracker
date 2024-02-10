<?php

use App\Http\Controllers\Api\AccountTransfersController;
use App\Http\Controllers\Api\TransactionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::as('api.')->middleware('auth:sanctum')->group(function () {
    Route::get('/account-transfers', [AccountTransfersController::class, 'index'])->name('account-transfers.index');
    Route::get('/transactions', [TransactionsController::class, 'index'])->name('transactions.index');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
