<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_requires_valid_fields_and_consent(): void
    {
        $this->post('/register', ['name' => 'X', 'email' => 'bad', 'phone' => '123', 'password' => 'short', 'role' => 'owner'])
            ->assertSessionHasErrors(['email', 'phone', 'password', 'terms']);
        $this->assertGuest();
    }

    public function test_user_registers_and_verifies_with_demo_otp(): void
    {
        $this->post('/register', [
            'name' => 'Rina Kusuma', 'email' => 'rina@example.com', 'phone' => '081299990000',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123', 'role' => 'owner', 'terms' => '1',
        ])->assertRedirect(route('otp.show'));

        $user = User::firstWhere('email', 'rina@example.com');
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->isVerified());
        $this->assertNotEquals('123456', $user->otp_code, 'OTP must be hashed at rest');

        // Platform is blocked until verified.
        $this->get('/overview')->assertRedirect(route('otp.show'));

        $this->post('/verify', ['code' => '000000'])->assertSessionHasErrors('code');
        $this->post('/verify', ['code' => '123456'])->assertRedirect(route('onboarding.show'));
        $this->assertTrue($user->fresh()->isVerified());
        $this->assertCount(3, $user->consents);
    }

    public function test_expired_otp_is_rejected(): void
    {
        $this->post('/register', [
            'name' => 'Rina', 'email' => 'r@example.com', 'phone' => '081299990000',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123', 'role' => 'owner', 'terms' => '1',
        ]);
        $this->travel(10)->minutes();
        $this->post('/verify', ['code' => '123456'])->assertSessionHasErrors('code');
    }

    public function test_login_and_logout(): void
    {
        $this->scenario('chacha');
        $this->post('/login', ['email' => 'chacha@tumbuh.demo', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => 'chacha@tumbuh.demo', 'password' => 'tumbuh123'])->assertRedirect(route('overview'));
        $this->get('/overview')->assertOk()->assertSee('Chacha Coffee');
        $this->post('/logout')->assertRedirect(route('landing'));
        $this->assertGuest();
    }

    public function test_demo_account_login(): void
    {
        $this->scenario('aisha');
        $this->post('/demo/login/aisha')->assertRedirect(route('overview'));
        $this->assertAuthenticated();
    }
}
