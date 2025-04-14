<?php

namespace Tests\Feature\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    public function test_user_can_login_with_valid_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'email',
            ]
        ]);
    }

    public function test_user_cannot_login_with_invalid_email()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexisting@example.com',
            'password' => 'any_password',
        ]);

        $response->assertStatus(422);

        $response->assertJson([
            'message' => 'There is no user with this email',
            'errors' => [
                'email' => ['There is no user with this email'],
            ],
        ]);
    }

    public function test_user_cannot_login_with_incorrect_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct_password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'The password entered is incorrect.',
            'error' => 'Incorrect password.',
        ]);
    }
}
