<?php

namespace App\Policies;

use App\Models\AccountTransfer;
use App\Models\User;

class AccountTransferPolicy
{
    public function update(User $user, AccountTransfer $accountTransfer): bool
    {
        return $user->id === $accountTransfer->user_id;
    }

    public function destroy(User $user, AccountTransfer $accountTransfer): bool
    {
        return $user->id === $accountTransfer->user_id;
    }
}
