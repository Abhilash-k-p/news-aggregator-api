<?php

namespace Tests\Feature\Api\V1\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PasswordResetControllerTest extends TestCase
{

    use RefreshDatabase;

   #[Test]
    public function it_sends_reset_link_for_existing_email()
    {
        // Create a user in the database
        $user = User::factory()->create([
            'email' => 'johndoe@example.com',
        ]);

        // Prepare request data
        $data = [
            'email' => 'johndoe@example.com',
        ];

        // Send the request to the API
        $response = $this->postJson('api/v1/password/forgot', $data);

        // Assert the response status and structure
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Reset token successfully generated',
            ]);
    }

    #[Test]
    public function it_returns_error_if_email_does_not_exist_for_reset_link()
    {
        // Prepare request data with an email that does not exist
        $data = [
            'email' => 'nonexistent@example.com',
        ];

        // Send the request to the API
        $response = $this->postJson('api/v1/password/forgot', $data);

        // Assert the response is a 422 error with validation message
        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The selected email is invalid.',
                'data' => ['The selected email is invalid.'],
            ]);
    }

    #[Test]
    public function it_returns_error_if_invalid_email_is_provided_for_reset_link()
    {
        // Prepare request data with invalid email
        $data = [
            'email' => 'invalid-email',
        ];

        // Send the request to the API
        $response = $this->postJson('api/v1/password/forgot', $data);

        // Assert the response is a 422 error with validation message
        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The email field must be a valid email address.',
                'data' => ['The email field must be a valid email address.'],
            ]);
    }

    #[Test]
    public function it_returns_error_if_token_generation_fails()
    {
        // Create a user in the database
        $user = User::factory()->create([
            'email' => 'johndoe@example.com',
        ]);

        // Prepare request data
        $data = [
            'email' => 'johndoe@example.com',
        ];

        // Mock the Password broker to simulate failure in token generation
        $passwordBrokerMock = Mockery::mock('Illuminate\Auth\Passwords\PasswordBroker');
        $passwordBrokerMock->shouldReceive('createToken')
            ->once()
            ->andThrow(new \Exception('Token generation failed')); // Simulate failure by throwing an exception

        // Mock the Password facade to return the mocked broker
        Password::shouldReceive('broker')
            ->once()
            ->andReturn($passwordBrokerMock);

        // Send the request to the API
        $response = $this->postJson('api/v1/password/forgot', $data);

        // Assert the response is a 500 error
        $response->assertStatus(500)
            ->assertJson([
                'message' => 'Token generation failed'
            ]);
    }

    #[Test]
    public function it_resets_password_successfully()
    {
        // Create a user in the database
        $user = User::factory()->create([
            'email' => 'johndoe@example.com',
            'password' => bcrypt('oldpassword'),
        ]);

        // Prepare request data with correct token and password
        $data = [
            'email' => 'johndoe@example.com',
            'token' => 'dummy-token',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        // Mock the Password::reset method to return PASSWORD_RESET status
        Password::shouldReceive('reset')
            ->once()
            ->andReturn(Password::PASSWORD_RESET);

        // Send the request to the API
        $response = $this->postJson('api/v1/password/reset', $data);

        // Assert the response status and success message
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Password reset successfully',
                'data' => [],
            ]);
    }

    #[Test]
    public function it_returns_error_if_password_reset_fails()
    {
        // Create a user in the database
        $user = User::factory()->create([
            'email' => 'johndoe@example.com',
            'password' => bcrypt('oldpassword'),
        ]);

        // Prepare request data with incorrect token
        $data = [
            'email' => 'johndoe@example.com',
            'token' => 'invalid-token',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        // Mock the Password::reset method to return a failure status
        Password::shouldReceive('reset')
            ->once()
            ->andReturn(Password::INVALID_TOKEN);

        // Send the request to the API
        $response = $this->postJson('api/v1/password/reset', $data);

        // Assert the response is an error
        $response->assertStatus(500)
            ->assertJson([
                'message' => 'Failed to reset password',
                'success' => false,
            ]);
    }
}
