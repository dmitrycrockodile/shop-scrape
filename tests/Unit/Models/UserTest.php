<?php 

namespace Tests\Unit\Models;

use App\Enums\UserRole;
use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_user_is_super_user()
    {
        $user = User::factory()->create([
            'role' => UserRole::SUPER_USER
        ]);

        $this->assertTrue($user->isSuperUser());
    }

    public function test_user_is_not_super_user()
    {
        $user = User::factory()->create([
            'role' => UserRole::REGULAR_USER
        ]);

        $this->assertFalse($user->isSuperUser());
    }

    public function test_user_is_regular_user()
    {
        $user = User::factory()->create([
            'role' => UserRole::REGULAR_USER
        ]);

        $this->assertTrue($user->isRegularUser());
    }

    public function test_user_is_not_regular_user()
    {
        $user = User::factory()->create([
            'role' => UserRole::SUPER_USER
        ]);

        $this->assertFalse($user->isRegularUser());
    }
}