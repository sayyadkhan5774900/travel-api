<?php

namespace Tests\Feature;

use App\Helpers\HttpResponse;
use App\Models\Role;
use App\Models\Travel;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTravelTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_can_not_access_admin_travel_create(): void
    {
        $response = $this->postJson('/api/v1/admin/travels');

        $response->assertStatus(HttpResponse::HTTP_UNAUTHORIZED);
    }

    public function test_no_admin_user_can_not_access_admin_travel_create(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'editor')->value('id'));

        $response = $this->actingAs($user)->postJson('/api/v1/admin/travels');

        $response->assertStatus(HttpResponse::HTTP_FORBIDDEN);
    }

    public function test_admin_user_can_access_and_create_travel_create(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'admin')->value('id'));

        $response = $this->actingAs($user)->postJson('/api/v1/admin/travels', [
            'name' => 'Travel',
        ]);

        $response->assertStatus(HttpResponse::HTTP_UNPROCESSABLE_ENTITY);

        $response = $this->actingAs($user)->postJson('/api/v1/admin/travels', [
            'name' => 'Travel',
            'is_public' => true,
            'slug' => 'travel',
            'description' => 'Travel description',
            'number_of_days' => 5,
        ]);

        $response->assertStatus(HttpResponse::HTTP_CREATED);

        $response = $this->get('/api/v1/travels');
        $response->assertJsonFragment(['name' => 'Travel']);

    }

    public function test_login_user_can_access_and_update_travel(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'admin')->value('id'));
        $travel = Travel::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/v1/admin/travels/'.$travel->id, [
            'name' => 'Travel',
        ]);

        $response->assertStatus(HttpResponse::HTTP_UNPROCESSABLE_ENTITY);

        $response = $this->actingAs($user)->putJson('/api/v1/admin/travels/'.$travel->id, [
            'name' => 'Travel Changed',
            'is_public' => true,
            'slug' => 'travel',
            'description' => 'Travel description',
            'number_of_days' => 5,
        ]);

        $response->assertStatus(HttpResponse::HTTP_OK);

        $response = $this->get('/api/v1/travels');
        $response->assertJsonFragment(['name' => 'Travel Changed']);

    }
}
