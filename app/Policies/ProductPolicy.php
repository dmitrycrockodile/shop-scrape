<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;

class ProductPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function delete(User $user)
    {
        return $user->isSuperUser();
    }

    public function update(User $user, Product $product)
    {
        if ($user->isSuperUser()) {
            return true;
        }

        $userRetailers = $user->retailers;

        foreach ($userRetailers as $retailer) {
            if ($retailer->products->contains($product)) {
                return true;
            }
        }

        return false;
    }
}
