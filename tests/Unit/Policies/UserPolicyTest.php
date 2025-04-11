<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\UserPolicy;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    protected UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserPolicy();
    }

    public function test_manage_users_returns_true_for_super_user()
    {
        $user = User::factory()->create(['role' => 'super_user']);

        $this->assertTrue($this->policy->manageUsers($user));
    }

    public function test_manage_users_returns_false_for_non_super_user()
    {
        $user = User::factory()->create(['role' => 'regular_user']);

        $this->assertFalse($this->policy->manageUsers($user));
    }
}