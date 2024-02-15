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

    public function updateTransaction(Transaction $transaction, TransactionDTO $DTO)
    {
        DB::transaction(function () use ($transaction, $DTO) {
            $oldAccount = $transaction->account;
            $amountDifference = $DTO->amount - $transaction->amount;
            $multiplier = $transaction->category->type === CategoryType::INCOME ? 1 : -1;

            $transaction->update($DTO->toArray());

            $newAccount = $transaction->fresh()->account;
            if ($newAccount->is($oldAccount)) {
                $account = $transaction->fresh()->account;
                $account->addBalance($multiplier * $amountDifference);
            } else {
                $oldAccount->addBalance(-$multiplier * $DTO->amount);
                $newAccount->addBalance($multiplier * $DTO->amount);
            }
        });
    }
}
