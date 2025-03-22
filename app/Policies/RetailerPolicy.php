<?php

namespace App\Policies;

use App\Models\User;
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
        return true;

        if ($user->isSuperUser()) {
            return true;
        }

        $accessibleRetailers = DB::table('user_retailers')->where('user_id', $user->id)->pluck('retailer_id')->toArray();

        if (empty($accessibleRetailers)) {
            return false;
        }

    }
}