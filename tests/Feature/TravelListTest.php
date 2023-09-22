<?php

namespace Tests\Feature;

use App\Helpers\HttpResponse;
use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelListTest extends TestCase
{
    use RefreshDatabase;

    public function test_travel_list_return_paginated_data_correctly(): void
    {
        Travel::factory(16)->create(['is_public' => true]);

        $response = $this->get('/api/v1/travels');

        $response->assertStatus(HttpResponse::HTTP_OK);

        $response->assertJsonCount(15, 'data');
        $response->assertJsonPath('meta.last_page', 2);
    }

    public function test_travel_list_show_only_public_record(): void
    {
        $travel = Travel::factory()->create(['is_public' => true]);
        Travel::factory()->create(['is_public' => false]);

        $response = $this->get('/api/v1/travels');

        $response->assertStatus(HttpResponse::HTTP_OK);

        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', $travel->name);
    }
}
