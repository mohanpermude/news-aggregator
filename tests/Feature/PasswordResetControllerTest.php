<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordResetControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a user for testing purposes
        $this->user = User::factory()->create(['email' => 'user@example.com']);
    }

    /** @test */
    public function it_sends_a_password_reset_link()
    {
        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['email' => 'user@example.com'])
            ->andReturn(Password::RESET_LINK_SENT);

        $response = $this->postJson('/api/password/email', [
            'email' => 'user@example.com',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => __('passwords.sent')]);
    }

    /** @test */
    public function it_fails_to_send_a_password_reset_link_for_invalid_email()
    {
        $response = $this->postJson('/api/password/email', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
        ->assertJsonPath('errors.email.0', 'The email must be a valid email address.');
    }

  
   /** @test */
    public function it_resets_the_user_password()
    {
        // Create a user
        $user = User::factory()->create(['password' => bcrypt('oldpassword')]);

        // Create a reset token for the user
        $token = Password::createToken($user);

        // Mock the reset method
        Password::shouldReceive('reset')
            ->once()
            ->with(
                [
                    'email' => $user->email,
                    'password' => 'new-password',
                    'password_confirmation' => 'new-password',
                    'token' => $token,
                ],
                \Mockery::type('Closure')
            )
            ->andReturnUsing(function ($data, $callback) use ($user) {
                // Call the closure to set the new password
                $callback($user); 
                return Password::PASSWORD_RESET; // Simulate a successful reset
            });

        // Call the API endpoint to reset the password
        $response = $this->postJson('/api/password/reset', [
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'token' => $token,
        ]);

        // Assert the response status and message
        $response->assertStatus(200)
                ->assertJson(['status' => __('passwords.reset')]);

        // Assert that the user's password has been updated
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password), 'Password was not updated correctly.');
    }

    /** @test */
    public function it_fails_to_reset_password_with_invalid_token()
    {
        $response = $this->postJson('/api/password/reset', [
            'email' => 'user@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'token' => 'invalid-token',
        ]);

        $response->assertStatus(400)
                 ->assertJsonStructure(['email']);
    }
}
