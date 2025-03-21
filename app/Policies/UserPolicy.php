<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\UserRole;

class UserPolicy {
    public function manageUsers(User $user): bool {
        return $user->role->value === UserRole::SUPER_USER->value;
    }
}
