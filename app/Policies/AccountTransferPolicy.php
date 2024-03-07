<?php

namespace App\Policies;

use App\Models\AccountTransfer;
use App\Models\User;

class AccountTransferPolicy
{
    public function create(): bool
    {
        return true;
    }

    public function update(User $user, AccountTransfer $accountTransfer): bool
    {
        return $user->id === $accountTransfer->user_id;
    }

    public function delete(User $user, AccountTransfer $accountTransfer): bool
    {
        return $user->id === $accountTransfer->user_id;
    }
}
