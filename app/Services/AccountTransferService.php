<?php

namespace App\Services;

use App\DTO\AccountTransferDTO;
use App\Models\Account;
use App\Models\AccountTransfer;
use DB;

class AccountTransferService
{
    public function transferBetweenAccounts(
        Account $accountFrom,
        Account $accountTo,
        AccountTransfer $transfer
    ): AccountTransfer {
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

        return $transfer->fresh();
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

    public function update(AccountTransfer $transfer, AccountTransferDTO $dto)
    {
        DB::transaction(function () use ($transfer, $dto) {
            $amountDifference = $dto->amount - $transfer->amount;
            $convertedAmountDifference = $dto->convertedAmount - $transfer->converted_amount;
            $accountFrom = $transfer->accountFrom;
            $accountTo = $transfer->accountTo;
            $oldAmount = $transfer->amount;
            $oldConvertedAmount = $transfer->converted_amount;

            $transfer->update($dto->toArray());
            $transfer = $transfer->fresh();

            $newAccountFrom = $transfer->accountFrom;
            $newAccountTo = $transfer->accountTo;

            if ($newAccountFrom->is($accountFrom)) {
                $accountFrom->update([
                    'balance' => $accountFrom->balance - $amountDifference,
                ]);
            } else {
                $accountFrom->update([
                    'balance' => $accountFrom->balance + $oldAmount,
                ]);

                $newAccountFrom->update([
                    'balance' => $newAccountFrom->balance - $transfer->amount,
                ]);
            }

            if ($newAccountTo->is($accountTo)) {
                $accountTo->update([
                    'balance' => $accountTo->balance + $convertedAmountDifference,
                ]);
            } else {
                $accountTo->update([
                    'balance' => $accountTo->balance - $oldConvertedAmount,
                ]);

                $newAccountTo->update([
                    'balance' => $newAccountTo->balance + $transfer->converted_amount,
                ]);
            }
        });
    }
}
