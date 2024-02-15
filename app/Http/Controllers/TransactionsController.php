<?php

namespace App\Http\Controllers;

use App\DTO\TransactionDTO;
use App\Enums\TransactionType;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Currency;
use App\Models\Transaction;
use App\Services\TransactionService;
use DB;

class TransactionsController extends Controller
{
    public function __construct(private readonly TransactionService $transactionService)
    {
    }

    public function store(StoreTransactionRequest $request)
    {
        $this->transactionService->createTransaction(
            TransactionDTO::fromRequest($request)
        );

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
