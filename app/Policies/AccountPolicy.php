<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function create(): bool
    {
        return true;
    }

    public function viewAny(): bool
    {
        return true;
    }

    public function update(User $user, Account $account): bool
    {
        return $user->id === $account->user_id;
    }

    public function delete(User $user, Account $account): bool
    {
        return $user->id === $account->user_id;
    }

    public function toggle(User $user, Account $account)
    {
        return $user->id === $account->user_id;
    }
}
