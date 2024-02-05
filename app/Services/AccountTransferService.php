<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountTransfer;
use DB;

class AccountTransferService
{
    public function transferBetweenAccounts(Account $accountFrom, Account $accountTo, AccountTransfer $transfer)
    {
        DB::transaction(function () use ($accountFrom, $accountTo, $transfer) {
            $transfer->fill([
                'account_from_id' => $accountFrom->id,
                'account_to_id' => $accountTo->id,
            ])->save();

            $accountFrom->update([
                'balance' => $accountFrom->balance - $transfer->amount,
            ]);

            $accountTo->update([
                'balance' => $accountTo->balance + $transfer->converted_amount,
            ]);
        });
    }

    public function delete(AccountTransfer $transfer)
    {
        DB::transaction(function () use ($transfer) {
            $transfer->delete();

            $transfer->accountFrom()->update([
                'balance' => $transfer->accountFrom->balance + $transfer->amount,
            ]);

            $transfer->accountTo()->update([
                'balance' => $transfer->accountTo->balance - $transfer->converted_amount,
            ]);
        });
    }
}
