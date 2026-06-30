<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginErrorDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_wrong_password_shows_error_message_on_login_page(): void
    {
        User::factory()->create([
            'email'    => 'student@example.com',
            'password' => 'secret-password',
        ]);

        $response = $this->from(route('login'))
            ->post(route('login.post'), [
                'email'    => 'student@example.com',
                'password' => 'wrong-password',
            ]);

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', __('auth.failed'));

        $this->followRedirects($response)
            ->assertSee(__('auth.failed'), false);
    }
}
