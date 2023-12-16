<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionsController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request)
    {
        Transaction::create([
            ...$request->validated(),
            'user_id' => Auth::user()->id,
            'currency_id' => Currency::findByCode($request->get('currency'))->id,
        ]);

        return redirect()->route('dashboard');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $transaction->update([
            ...$request->validated(),
            'currency_id' => Currency::findByCode($request->get('currency'))->id,
        ]);

        return redirect()->route('dashboard');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return redirect()->route('dashboard');
    }
}
