<?php

namespace Tests\Unit\Enums;

use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_super_user_text()
    {
        $this->assertEquals('Super user', UserRole::SUPER_USER->text());
    }

    public function test_regular_user_text()
    {
        $this->assertEquals('Regular user', UserRole::REGULAR_USER->text());
    }
}