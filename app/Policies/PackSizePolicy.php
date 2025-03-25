<?php

namespace App\Policies;

use App\Models\User;

class PackSizePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function update(User $user)
    {
        return $user->isSuperUser();
    }

    public function delete(User $user)
    {
        return $user->isSuperUser();
    }
}
