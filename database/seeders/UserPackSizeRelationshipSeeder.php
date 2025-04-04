<?php

namespace Database\Seeders;

use App\Models\PackSize;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserPackSize;

class UserPackSizeRelationshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $packSizes = PackSize::all();

        foreach ($packSizes as $packSize) {
            $assignedUsers = $users->random(1);
            foreach ($assignedUsers as $assignedUser) {
                UserPackSize::create([
                    'pack_size_id'  => $packSize->id,
                    'user_id' => $assignedUser->id,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}
