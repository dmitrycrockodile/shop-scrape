<?php

namespace Tests\Unit\Resources;

use App\Models\Currency;
use App\Models\User;
use App\Models\Retailer;
use App\Http\Resources\User\UserResource;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    public function test_user_resource_returns_expected_structure(): void
    {
        $currency = Currency::factory()->create();
        $retailer1 = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);
        $retailer2 = Retailer::factory()->create([
            'currency_id' => $currency->id
        ]);

        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'email_verified_at' => now(),
            'location' => 'New York',
        ]);
        $user->retailers()->attach([$retailer1->id, $retailer2->id]);

        if (method_exists($user->role, 'text')) {
            $expectedRole = $user->role->text();
        } else {
            $expectedRole = $user->role;
        }

        $resource = (new UserResource($user))->toArray(request());

        $this->assertEquals($user->id, $resource['id']);
        $this->assertEquals('John Doe', $resource['name']);
        $this->assertEquals('johndoe@example.com', $resource['email']);
        $this->assertTrue($resource['is_verified']);
        $this->assertEquals($expectedRole, $resource['role']);
        $this->assertEquals($user->isSuperUser(), $resource['admin']);
        $this->assertEquals('New York', $resource['location']);

        $this->assertCount(2, $resource['retailers']);
        $this->assertArrayHasKey('id', $resource['retailers'][0]);
        $this->assertArrayHasKey('title', $resource['retailers'][0]);
    }
}