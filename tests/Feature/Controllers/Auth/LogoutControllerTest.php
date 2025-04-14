<?php

namespace Tests\Feature\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LogoutControllerTest extends TestCase
{
    public function test_user_can_logout_successfully()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $token = $user->createToken('TestToken')->plainTextToken;
        $this->assertNotEmpty($user->tokens);
        
        $response = $this->actingAs($user, 'sanctum')
                         ->deleteJson('/api/logout');
    
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => true,
            'message' => 'user logged out successfully.'
        ]);

        $user->refresh();
        $this->assertCount(0, $user->tokens);
    }

    public function test_user_cannot_logout_without_being_authenticated()
    {
        $response = $this->deleteJson('/api/logout');
    
        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }
}
