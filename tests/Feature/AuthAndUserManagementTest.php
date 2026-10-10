<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAndUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Kobana Fried Chicken');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'kasir@test.com',
            'password' => Hash::make('password123'),
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'kasir@test.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@test.com',
            'password' => Hash::make('password123'),
            'role' => 'kasir',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@test.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHas('error');
    }

    public function test_admin_can_access_user_management(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/users');
        $response->assertStatus(200);
    }

    public function test_kasir_and_owner_cannot_access_user_management(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $response = $this->actingAs($kasir)->get('/users');
        $response->assertStatus(403);

        $owner = User::factory()->create([
            'role' => 'owner',
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->get('/users');
        $response->assertStatus(403);
    }

    public function test_admin_can_create_new_user(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Kasir Baru',
            'email' => 'kasirbaru@test.com',
            'password' => 'secret123',
            'role' => 'kasir',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'email' => 'kasirbaru@test.com',
            'role' => 'kasir',
            'is_active' => true,
        ]);
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect('/profile/password');
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }
}
