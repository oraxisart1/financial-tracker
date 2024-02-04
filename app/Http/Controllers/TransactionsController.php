<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Account;
use App\Models\Currency;
use App\Models\Transaction;
use DB;
use Illuminate\Support\Facades\Auth;

class TransactionsController extends Controller
{
    public function store(StoreTransactionRequest $request)
    {
        $transaction = Transaction::make([
            ...$request->validated(),
            'user_id' => Auth::user()->id,
            'currency_id' => Currency::findByCode($request->get('currency'))->id,
        ]);

        Account::find($request->get('account_id'))
            ->addTransaction($transaction);

        return redirect()->back();
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        DB::transaction(function () use ($transaction, $request) {
            $oldAccount = $transaction->account;
            $amountDifference = $request->get('amount') - $transaction->amount;
            $multiplier = $transaction->type === TransactionType::INCOME ? 1 : -1;

            $transaction->update([
                ...$request->validated(),
                'currency_id' => Currency::findByCode($request->get('currency'))->id,
            ]);

            $newAccount = $transaction->fresh()->account;
            if ($newAccount->is($oldAccount)) {
                $account = $transaction->fresh()->account;
                $account->update([
                    'balance' => $account->balance + ($multiplier * $amountDifference),
                ]);
            } else {
                $oldAccount->update([
                    'balance' => $oldAccount->balance + (-$multiplier * $request->get('amount')),
                ]);

                $newAccount->update([
                    'balance' => $newAccount->balance + ($multiplier * $request->get('amount')),
                ]);
            }
        });

        return redirect()->back();
    }

    public function destroy(Transaction $transaction)
    {
        $multiplier = $transaction->type === TransactionType::INCOME ? -1 : 1;

        DB::transaction(function () use ($transaction, $multiplier) {
            $transaction->delete();

            $transaction->account->update([
                'balance' => $transaction->account->balance + ($multiplier * $transaction->amount),
            ]);
        });

        return redirect()->back();
    }
}
