<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Account;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionsController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request)
    {
        DB::transaction(function () use ($request) {
            Transaction::create([
                ...$request->validated(),
                'user_id' => Auth::user()->id,
                'currency_id' => Currency::findByCode($request->get('currency'))->id,
            ]);

            $account = Account::find($request->get('account_id'));
            $type = TransactionType::from($request->get('type'));
            $amount = ($type === TransactionType::INCOME ? 1 : -1) * $request->get('amount');
            $account->update([
                'balance' => $account->balance + $amount,
            ]);
        });

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
