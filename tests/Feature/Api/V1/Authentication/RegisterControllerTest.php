<?php

namespace Tests\Feature\Api\V1\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

   #[Test]
    public function it_registers_a_new_user_successfully()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson(route('register'), $data);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User Registered successfully.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'id', 'name', 'email', 'created_at', 'updated_at'
                    ],
                    'token',
                ]
            ]);

        // Assert that user was created in the database
        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@example.com',
        ]);

        // Assert that the password is hashed
        $user = User::where('email', 'johndoe@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    #[Test]
    public function it_returns_validation_error_if_name_is_missing()
    {
        $data = [
            'email' => 'johndoe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson(route('register'), $data);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,  // Check success key is false
                'message' => 'The name field is required.',  // The message returned
            ]);
    }

    #[Test]
    public function it_returns_validation_error_if_email_is_missing()
    {
        $data = [
            'name' => 'John Doe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson(route('register'), $data);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,  // Check success key is false
                'message' => 'The email field is required.',  // The message returned
            ]);
    }

    #[Test]
    public function it_returns_validation_error_if_email_is_already_registered()
    {
        // Create a user first
        $existingUser = User::create([
            'name' => 'Existing User',
            'email' => 'johndoe@example.com',
            'password' => Hash::make('password123'),
        ]);

        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',  // Same email as existing user
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson(route('register'), $data);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,  // Check success key is false
                'message' => 'The email has already been taken.',  // The message returned
            ]);
    }

    #[Test]
    public function it_returns_validation_error_if_password_confirmation_does_not_match()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password124',  // Mismatch
        ];

        $response = $this->postJson(route('register'), $data);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,  // Check success key is false
                'message' => 'The password field confirmation does not match.',  // The message returned
            ]);
    }
}
