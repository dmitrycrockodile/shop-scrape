<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Enums\UserRole;

class SuperUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate([
            'name' => 'Super user2',
            'email' => 'super2@user.com',
            'password' => '5UPER_user_passw0rd',
            'role' => UserRole::SUPER_USER
        ]);
    }
}
