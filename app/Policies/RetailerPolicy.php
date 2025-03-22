<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Retailer;
use Illuminate\Support\Facades\DB;

class RetailerPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Check if the user has any assigned retailers.
     *
     * @param User $user
     * 
     * @return bool
    */
    public function getMetrics(User $user): bool {
        if ($user->isSuperUser()) {
            return true;
        }

        $accessibleRetailers = DB::table('user_retailers')->where('user_id', $user->id)->pluck('retailer_id')->toArray();

        if (empty($accessibleRetailers)) {
            return false;
        }

        return true;
    }

    public function seeAll(User $user): bool {
        return $user->isSuperUser();
    }

    public function store(User $user): bool {
        return $user->isSuperUser();
    }

    public function update(User $user): bool {
        return $user->isSuperUser();
    }

    public function delete(User $user): bool {
        return $user->isSuperUser();
    }

    public function seeProducts(User $user, Retailer $retailer): bool {
        if ($user->isSuperUser()) {
            return true;
        }

        return DB::table('user_retailers')
            ->where('user_id', $user->id)
            ->where('retailer_id', $retailer->id)
            ->exists();
    }

    public function addProducts(User $user, Retailer $retailer): bool {
        if ($user->isSuperUser()) {
            return true;
        }

        return DB::table('user_retailers')
            ->where('user_id', $user->id)
            ->where('retailer_id', $retailer->id)
            ->exists();
    }
}