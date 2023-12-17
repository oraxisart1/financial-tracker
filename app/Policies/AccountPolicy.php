<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function update(User $user, Account $category): bool
    {
        return $user->id === $category->user_id;
    }

    public function destroy(User $user, Account $category): bool
    {
        return $user->id === $category->user_id;
    }
}
