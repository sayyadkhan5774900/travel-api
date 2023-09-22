<?php

namespace Tests\Feature;

use App\Helpers\HttpResponse;
use App\Models\Tour;
use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TourListTest extends TestCase
{

    use RefreshDatabase;

    public function test_tour_list_by_slug_return_correct_tours(): void
    {
        $travel = Travel::factory()->create(['is_public' => true]);
        $tour = Tour::factory()->create(['travel_id' => $travel->id]);

        $response = $this->get('/api/v1/travels/' . $travel->slug . '/tours');

        $response->assertStatus(HttpResponse::HTTP_OK);

        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $tour->id]);
    }

    public function test_tour_price_is_shown_correctly(): void
    {
        $travel = Travel::factory()->create(['is_public' => true]);
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 33.35,
        ]);


        $response = $this->get('/api/v1/travels/' . $travel->slug . '/tours');

        $response->assertStatus(HttpResponse::HTTP_OK);

        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['price' => '33.35']);
    }

    public function test_tour__list_return_paginated_data_correctly(): void
    {
        $travel = Travel::factory()->create(['is_public' => true]);
        $tour = Tour::factory(16)->create(['travel_id' => $travel->id]);

        $response = $this->get('/api/v1/travels/' . $travel->slug . '/tours');

        $response->assertStatus(HttpResponse::HTTP_OK);

        $response->assertJsonCount(15,'data');
        $response->assertJsonPath('meta.last_page',2);
    }

    public function test_tour__list_sort_by_starting_date_correctly(): void
    {
        $travel = Travel::factory()->create(['is_public' => true]);
        $later_tour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now()->addDays(2),
            'ending_date' => now()->addDays(3),
        ]);
        $earlier_tour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now(),
            'ending_date' => now()->addDays(1),
        ]);

        $response = $this->get('/api/v1/travels/' . $travel->slug . '/tours');

        $response->assertStatus(HttpResponse::HTTP_OK);

        $response->assertJsonPath('data.0.id',$earlier_tour->id);
        $response->assertJsonPath('data.1.id',$later_tour->id);
    }

    public function test_tour__list_sort_by_price_correctly(): void
    {
        $travel = Travel::factory()->create(['is_public' => true]);
        $expensive_tour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 200,
        ]);
        $cheap_later_tour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 100,
            'starting_date' => now()->addDays(2),
            'ending_date' => now()->addDays(3),
        ]);
        $cheap_earlier_tour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 100,
            'starting_date' => now(),
            'ending_date' => now()->addDays(1),
        ]);

        $response = $this->get('/api/v1/travels/' . $travel->slug . '/tours?sortBy=price&orderBy=asc');

        $response->assertStatus(HttpResponse::HTTP_OK);

        $response->assertJsonPath('data.0.id',$cheap_earlier_tour->id);
        $response->assertJsonPath('data.1.id',$cheap_later_tour->id);
        $response->assertJsonPath('data.2.id',$expensive_tour->id);
    }

    public function test_tour__list_filter_by_price_correctly(): void
    {
        $travel = Travel::factory()->create(['is_public' => true]);
        $expensive_tour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 200,
        ]);
        $cheap_tour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 100,
        ]);

        $url = '/api/v1/travels/' . $travel->slug . '/tours';

        $response = $this->get($url.'?priceFrom=100');
        $response->assertStatus(HttpResponse::HTTP_OK);
        $response->assertJsonCount(2,'data');
        $response->assertJsonFragment(['id' => $cheap_tour->id]);
        $response->assertJsonFragment(['id' => $expensive_tour->id]);

        $response = $this->get($url.'?priceFrom=150');
        $response->assertStatus(HttpResponse::HTTP_OK);
        $response->assertJsonCount(1,'data');
        $response->assertJsonMissing(['id' => $cheap_tour->id]);
        $response->assertJsonFragment(['id' => $expensive_tour->id]);

        $response = $this->get($url.'?priceFrom=250');
        $response->assertStatus(HttpResponse::HTTP_OK);
        $response->assertJsonCount(0,'data');

        $response = $this->get($url.'?priceTo=200');
        $response->assertStatus(HttpResponse::HTTP_OK);
        $response->assertJsonCount(2,'data');
        $response->assertJsonFragment(['id' => $cheap_tour->id]);
        $response->assertJsonFragment(['id' => $expensive_tour->id]);

        $response = $this->get($url.'?priceTo=150');
        $response->assertStatus(HttpResponse::HTTP_OK);
        $response->assertJsonCount(1,'data');
        $response->assertJsonMissing(['id' => $expensive_tour->id]);
        $response->assertJsonFragment(['id' => $cheap_tour->id]);

        $response = $this->get($url.'?priceTo=50');
        $response->assertStatus(HttpResponse::HTTP_OK);
        $response->assertJsonCount(0,'data');
    }

    public function test_tour__list_filter_by_starting_date_correctly(): void
    {
        $travel = Travel::factory()->create(['is_public' => true]);
        $later_tour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now()->addDays(2),
            'ending_date' => now()->addDays(3),
        ]);

        $earlier_tour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now(),
            'ending_date' => now()->addDays(1),
        ]);
        $url = '/api/v1/travels/' . $travel->slug . '/tours';

        $response = $this->get($url.'?dateFrom='.now());
        $response->assertStatus(HttpResponse::HTTP_OK);
        $response->assertJsonCount(2,'data');
        $response->assertJsonFragment(['id' => $earlier_tour->id]);
        $response->assertJsonFragment(['id' => $later_tour->id]);

        $response = $this->get($url.'?dateFrom='.now()->addDay());
        $response->assertStatus(HttpResponse::HTTP_OK);
        $response->assertJsonCount(1,'data');
        $response->assertJsonMissing(['id' => $earlier_tour->id]);
        $response->assertJsonFragment(['id' => $later_tour->id]);

        $response = $this->get($url.'?dateFrom='.now()->addDays(5));
        $response->assertStatus(HttpResponse::HTTP_OK);
        $response->assertJsonCount(0,'data');

        $response = $this->get($url.'?dateTo='.now()->addDays(5));
        $response->assertStatus(HttpResponse::HTTP_OK);
        $response->assertJsonCount(2,'data');
        $response->assertJsonFragment(['id' => $earlier_tour->id]);
        $response->assertJsonFragment(['id' => $later_tour->id]);

        $response = $this->get($url.'?dateTo='.now()->addDay());
        $response->assertStatus(HttpResponse::HTTP_OK);
        $response->assertJsonCount(1,'data');
        $response->assertJsonMissing(['id' => $later_tour->id]);
        $response->assertJsonFragment(['id' => $earlier_tour->id]);

        $response = $this->get($url.'?dateTo='.now()->subDay());
        $response->assertStatus(HttpResponse::HTTP_OK);
        $response->assertJsonCount(0,'data');

    }

    // TODO:: fix this error later
    // public function test_tour__list_return_validation_error(): void
    // {
    //     $travel = Travel::factory()->create(['is_public' => true]);

    //     $url = '/api/v1/travels/' . $travel->slug . '/tours';

    //     $response = $this->get($url.'?dateFrom=abcde');
    //     $response->assertStatus(422);

    //     $response = $this->get($url.'?priceFrom=abcde');
    //     $response->assertStatus(422);
    // }
}
