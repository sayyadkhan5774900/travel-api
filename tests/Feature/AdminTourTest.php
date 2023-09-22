<?php

namespace Tests\Feature;

use App\Helpers\HttpResponse;
use App\Models\Role;
use App\Models\Tour;
use App\Models\Travel;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminTourTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_can_not_access_admin_tour_create(): void
    {
        $travel = Travel::factory()->create();

        $response = $this->postJson('/api/v1/admin/travels/'.$travel->id.'/tours');

        $response->assertStatus(HttpResponse::HTTP_UNAUTHORIZED);
    }

    public function test_no_admin_user_can_not_access_admin_tour_create(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name','editor')->value('id'));
        $travel = Travel::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/admin/travels/'.$travel->id.'/tours');

        $response->assertStatus(HttpResponse::HTTP_FORBIDDEN);
    }

    public function test_admin_user_can_access_and_create_tour(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name','admin')->value('id'));
        $travel = Travel::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/admin/travels/'.$travel->id.'/tours',[
            'name' => 'Travel',
        ]);

        $response->assertStatus(HttpResponse::HTTP_UNPROCESSABLE_ENTITY);

        $response = $this->actingAs($user)->postJson('/api/v1/admin/travels/'.$travel->id.'/tours',[
                'name' => 'Toure 12123',
                'starting_date' => '2023-09-15',
                'ending_date' => '2023-09-20',
                'price' => 5454,
            ]);

        $response->assertStatus(HttpResponse::HTTP_CREATED);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');
        $response->assertJsonFragment(['name' => 'Toure 12123']);

    }

    public function test_authenticated_user_can_access_and_update_tour(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name','admin')->value('id'));

        $travel = Travel::factory()->create();

        $tour = Tour::factory()->create(
            [
                'travel_id' => $travel->id
            ]
        );

        $response = $this->actingAs($user)->putJson('/api/v1/admin/tours/'.$tour->id,[
            'name' => 'Travel',
        ]);

        $response->assertStatus(HttpResponse::HTTP_UNPROCESSABLE_ENTITY);

        $response = $this->actingAs($user)->putJson('/api/v1/admin/tours/'.$tour->id,[
            'name' => 'Tour 12123 Changed',
            'starting_date' => '2023-09-15',
            'ending_date' => '2023-09-20',
            'price' => 5454,
        ]);

        $response->assertStatus(HttpResponse::HTTP_OK);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');
        $response->assertJsonFragment(['name' => 'Tour 12123 Changed']);

    }
}
