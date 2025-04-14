<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\User;
use App\Models\Retailer;
use Illuminate\Http\Response;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    public function test_super_user_can_see_users()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user);

        User::factory()->count(3)->create();

        $response = $this->getJson('/api/users');

        $response->assertOk()
                 ->assertJsonCount(3, 'data');
    }

    public function test_regular_user_cannot_see_users()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        User::factory()->count(3)->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_super_user_can_create_a_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user);

        $data = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'UniQUepa55word_ztm',
            'password_confirmation' => 'UniQUepa55word_ztm',
            'role' => 'regular_user',
            'location' => 'Chicago',
        ];

        $response = $this->postJson('/api/users', $data);

        $response->assertCreated()
                 ->assertJsonPath('data.email', $data['email']);
    }

    public function test_regular_user_cannot_create_a_user()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'UniQUepa55word_ztm',
            'password_confirmation' => 'UniQUepa55word_ztm',
            'role' => 'regular_user',
            'location' => 'Chicago',
        ];

        $response = $this->postJson('/api/users', $data);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_super_user_can_update_a_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user);

        $regularUser = User::factory()->create();

        $response = $this->putJson("/api/users/{$regularUser->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertOk()
                 ->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_regular_user_cannot_update_a_user()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $regularUser = User::factory()->create();

        $response = $this->putJson("/api/users/{$regularUser->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_super_user_can_assign_retailers_to_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        $regularUser = User::factory()->create();
        
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $retailers = Retailer::factory()->count(2)->create([
            'currency_id' => $currency->id
        ]);

        $response = $this->postJson("/api/users/{$regularUser->id}/assign-retailers", [
            'retailers' => $retailers->map(fn ($r) => ['id' => $r->id])->toArray()
        ]);

        $response->assertOk()
                 ->assertJsonCount(2, 'data');
    }

    public function test_regular_user_cannot_assign_retailers_to_user()
    {
        $user = User::factory()->create();
        $regularUser = User::factory()->create();
        
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $retailers = Retailer::factory()->count(2)->create([
            'currency_id' => $currency->id
        ]);

        $response = $this->postJson("/api/users/{$regularUser->id}/assign-retailers", [
            'retailers' => $retailers->map(fn ($r) => ['id' => $r->id])->toArray()
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_it_does_not_allow_assigning_retailers_to_super_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        $superUser = User::factory()->create([
            'role' => 'super_user'
        ]);
        
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $retailers = Retailer::factory()->count(2)->create([
            'currency_id' => $currency->id
        ]);

        $response = $this->postJson("/api/users/{$superUser->id}/assign-retailers", [
            'retailers' => $retailers->map(fn ($r) => ['id' => $r->id])->toArray()
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_super_user_can_revoke_retailers_from_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        $regularUser = User::factory()->create();
        
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $retailers = Retailer::factory()->count(2)->create([
            'currency_id' => $currency->id
        ]);
        $regularUser->retailers()->attach($retailers);

        $response = $this->postJson("/api/users/{$regularUser->id}/revoke-retailers", [
            'retailers' => $retailers->map(fn ($r) => ['id' => $r->id])->toArray()
        ]);

        $response->assertOk()
                 ->assertJsonCount(0, 'data');
    }

    public function test_regular_user_cannot_revoke_retailers_from_user()
    {
        $user = User::factory()->create();
        $regularUser = User::factory()->create();
        
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $retailers = Retailer::factory()->count(2)->create([
            'currency_id' => $currency->id
        ]);
        $regularUser->retailers()->attach($retailers);

        $response = $this->postJson("/api/users/{$regularUser->id}/revoke-retailers", [
            'retailers' => $retailers->map(fn ($r) => ['id' => $r->id])->toArray()
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_super_user_cannot_revoke_retailers_from_super_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        $superUser = User::factory()->create([
            'role' => 'super_user'
        ]);
        
        Sanctum::actingAs($user);

        $currency = Currency::factory()->create();
        $retailers = Retailer::factory()->count(2)->create([
            'currency_id' => $currency->id
        ]);
        $superUser->retailers()->attach($retailers);

        $response = $this->postJson("/api/users/{$superUser->id}/revoke-retailers", [
            'retailers' => $retailers->map(fn ($r) => ['id' => $r->id])->toArray()
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_super_user_can_delete_a_user()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_regular_user_cannot_delete_a_user()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}