<?php

namespace App\Services;

use App\DTO\TransactionDTO;
use App\Enums\CategoryType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function createTransaction(TransactionDTO $DTO): Transaction
    {
        $category = Category::find($DTO->categoryId);
        $multiplier = $category->type === CategoryType::INCOME ? 1 : -1;

        $account = Account::find($DTO->accountId);
        $account->addBalance(($multiplier * $DTO->amount));
        return Transaction::create($DTO->toArray());
    }

    public function deleteTransaction(Transaction $transaction)
    {
        $multiplier = $transaction->category->type === CategoryType::INCOME ? -1 : 1;

        DB::transaction(function () use ($transaction, $multiplier) {
            $transaction->delete();

            $transaction->account->addBalance($multiplier * $transaction->amount);
        });
    }
}
