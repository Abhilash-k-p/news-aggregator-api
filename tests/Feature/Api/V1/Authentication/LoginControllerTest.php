<?php

namespace Tests\Feature\Api\V1\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login success.
     */
    public function test_login_success()
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['user', 'token'],
                'message',
            ]);

        $this->assertNotNull($response['data']['token']);
    }

    /**
     * Test login with invalid credentials.
     */
    public function test_login_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid email or password',
            ]);
    }

    /**
     * Test login validation failure.
     */
    public function test_login_validation_failure()
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    /**
     * Test login when user doesn't exist.
     */
    public function test_login_user_does_not_exist()
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'The selected email is invalid.',
            ]);
    }

    /**
     * Test logout success.
     */
    public function test_logout_success()
    {
        $user = User::factory()->create();

        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User Logged Out Successfully.',
            ]);

        $this->assertCount(0, $user->tokens);
    }

    /**
     * Test logout without authentication.
     */
    public function test_logout_unauthenticated()
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'You are not authenticated. Please log in to continue.',
            ]);
    }
}
