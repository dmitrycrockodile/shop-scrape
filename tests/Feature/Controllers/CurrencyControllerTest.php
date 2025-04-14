<?php

namespace Tests\Feature\Controllers;

use App\Models\Currency;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use Tests\TestCase;

class CurrencyControllerTest extends TestCase
{
    public function test_index_returns_paginated_currencies_with_success_response()
    {
        $user = User::factory()->create([
            'role' => 'regular_user'
        ]);
        Sanctum::actingAs($user, ['*']);

        Currency::create([
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
        ]);
        Currency::create([
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => '€',
        ]);

        $response = $this->getJson('/api/currencies?dataPerPage=10&page=1');

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
        ]);
        $response->assertJsonFragment([
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => '€',
        ]);

        $response->assertJsonStructure([
            'meta' => [
                'current_page',
                'per_page',
                'last_page',
                'total',
                'links',
            ],
        ]);

        $expectedMessage = trans('messages.index.success', ['attribute' => 'currency']);
        $this->assertEquals($expectedMessage, $response->json('message'));
    }

    public function test_index_returns_error_when_unauthorized()
    {
        $response = $this->getJson('/api/currencies?dataPerPage=10&page=1');

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $response->json('message'));
    }
}