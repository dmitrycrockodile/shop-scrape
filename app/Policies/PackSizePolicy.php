<?php

namespace App\Policies;

use App\Models\PackSize;
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

    public function update(User $user, PackSize $packSize)
    {
        if ($user->isSuperUser()) {
            return true;
        }

        $userPackSizes = $user->packSizes;
        if ($userPackSizes->contains($packSize)) {
            return true;
        }

        return false;
    }

    public function delete(User $user)
    {
        return $user->isSuperUser();
    }
}
