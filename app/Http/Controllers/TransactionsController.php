<?php

namespace App\Http\Controllers;

use App\DTO\TransactionDTO;
use App\Enums\TransactionType;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Services\TransactionService;

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
        $this->transactionService->updateTransaction(
            $transaction,
            TransactionDTO::fromRequest($request)
        );

        return redirect()->back();
    }

    public function destroy(Transaction $transaction)
    {
        $this->transactionService->deleteTransaction($transaction);

        return redirect()->back();
    }
}
