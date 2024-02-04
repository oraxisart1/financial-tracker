<?php

namespace App\Services;

use App\Models\Account;
use DB;

class AccountService
{
    public const DELETE_CASCADE_MODE = 'delete_all';

    public const DELETE_ACCOUNT_ONLY_MODE = 'delete_account';

    public function delete(Account $account, string $deleteMode)
    {
        DB::transaction(function () use ($account, $deleteMode) {
            if ($deleteMode === self::DELETE_CASCADE_MODE) {
                $account->transactions()->delete();
                $account->transfersFrom()->delete();
                $account->transfersTo()->delete();
                $account->forceDelete();
            } else {
                $account->delete();
            }
        });
    }
}
