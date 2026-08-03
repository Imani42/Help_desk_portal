<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private const OLD_PASSWORD = 'Pass12@word';
    private const NEW_PASSWORD = 'New34@word';

    public function test_login_shows_reset_guidance_after_three_unsuccessful_attempts(): void
    {
        User::create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => Hash::make(self::OLD_PASSWORD),
            'role' => 'customer',
            'is_approved' => true,
        ]);

        foreach (range(1, 3) as $attempt) {
            $response = $this->from('/login')->post('/login', [
                'email' => 'customer@example.com',
                'password' => 'Wrong12@word',
            ]);
        }

        $response->assertRedirect('/login')
            ->assertSessionHas('show_forgot_password', true)
            ->assertSessionHasErrors('email');
    }

    public function test_password_reset_requires_reapproval_for_non_admin_users(): void
    {
        $user = User::create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => Hash::make(self::OLD_PASSWORD),
            'role' => 'customer',
            'is_approved' => true,
        ]);
        $token = Password::broker()->createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertRedirect('/login');

        $user->refresh();
        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->password));
        $this->assertFalse($user->is_approved);
    }

    public function test_password_reset_does_not_deactivate_an_admin(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make(self::OLD_PASSWORD),
            'role' => 'admin',
            'is_approved' => true,
        ]);
        $token = Password::broker()->createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertRedirect('/login');

        $this->assertTrue($user->fresh()->is_approved);
    }
}
