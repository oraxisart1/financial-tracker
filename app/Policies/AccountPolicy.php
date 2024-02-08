<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function update(User $user, Account $account): bool
    {
        return $user->id === $account->user_id;
    }

    public function destroy(User $user, Account $account): bool
    {
        return $user->id === $account->user_id;
    }

    public function toggle(User $user, Account $account)
    {
        return $user->id === $account->user_id;
    }
}
