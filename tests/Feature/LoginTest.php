<?php

namespace Tests\Feature;

use App\Helpers\HttpResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_with_correct_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(HttpResponse::HTTP_OK);
        $response->assertJsonStructure(['token']);
    }

    public function test_login_returns_422_with_incorrect_credentials(): void
    {
        $response = $this->post('/api/v1/login', [
            'email' => 'ali@gmail.com',
            'password' => 'password',
        ]);

        $response->assertStatus(HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
    }
}
