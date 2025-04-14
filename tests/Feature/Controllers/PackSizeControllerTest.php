<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\PackSize;
use Laravel\Sanctum\Sanctum;

class PackSizeControllerTest extends TestCase
{
    public function test_super_user_can_get_paginated_pack_sizes()
    {
        $user = User::factory()->create([
            'role' => 'super_user'
        ]);
        Sanctum::actingAs($user);

        PackSize::factory()->count(4)->create();

        $response = $this->getJson('/api/pack-sizes?dataPerPage=5&page=1');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['name', 'weight', 'weight_unit', 'amount']
                ],
                'message',
                'meta' => ['current_page', 'per_page', 'last_page', 'total', 'links'],
            ]);
    }

    public function test_regular_user_can_see_their_pack_sizes()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        Sanctum::actingAs($user);

        $userPackSizes = PackSize::factory()->count(3)->create();
        $user->packSizes()->attach($userPackSizes);

        PackSize::factory()->create();

        $response = $this->getJson('/api/pack-sizes?dataPerPage=10&page=1');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['name', 'weight', 'weight_unit', 'amount']
                ],
                'message',
                'meta' => ['current_page', 'per_page', 'last_page', 'total', 'links'],
            ]);
    }

    public function test_user_can_store_a_pack_size()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        Sanctum::actingAs($user);

        $payload = [
            'name' => 'Cola 6 pack',
            'weight' => 200,
            'weight_unit' => 'ml', 
            'amount' => 20
        ];

        $response = $this->postJson('/api/pack-sizes', $payload);

        $response->assertCreated()
            ->assertJsonFragment([
                'name' => 'Cola 6 pack',
                'weight' => 200,
                'weight_unit' => 'ml', 
                'amount' => 20
            ]);

        $this->assertDatabaseHas('pack_sizes', $payload);
    }

    public function test_user_can_update_a_pack_size_they_have_access_to()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        Sanctum::actingAs($user);

        $packSize = PackSize::factory()->create([
            'name' => 'Cola 6 pack',
            'weight' => 200,
            'weight_unit' => 'ml', 
            'amount' => 20
        ]);
        $user->packSizes()->attach($packSize);

        $payload = [
            'name' => 'Cola 2 pack',
            'weight' => 330,
            'weight_unit' => 'ml', 
            'amount' => 2
        ];
        $user->packSizes()->attach($packSize);

        $response = $this->putJson("/api/pack-sizes/{$packSize->id}", $payload);

        $response->assertOk()
            ->assertJsonFragment($payload);

        $this->assertDatabaseHas('pack_sizes', $payload);
    }

    public function test_user_can_delete_a_pack_size_if_authorized()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        Sanctum::actingAs($user);

        $packSize = PackSize::factory()->create([
            'name' => 'Cola 6 pack',
            'weight' => 200,
            'weight_unit' => 'ml', 
            'amount' => 20
        ]);

        $user->packSizes()->attach($packSize);

        $response = $this->deleteJson("/api/pack-sizes/{$packSize->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'message' => __('messages.destroy.success', ['attribute' => 'pack size']),
            ]);

        $this->assertDatabaseMissing('pack_sizes', ['id' => $packSize->id]);
    }
}