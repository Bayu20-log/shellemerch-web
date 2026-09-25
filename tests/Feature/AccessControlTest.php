<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_admin_and_customer_areas(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/admin/login');
        $this->get('/pesanan')->assertRedirect('/login');
    }

    public function test_customer_cannot_open_any_admin_page(): void
    {
        $customer = User::factory()->create();

        foreach (['/admin/dashboard', '/admin/products', '/admin/pin-sizes', '/kontak', '/admin/news'] as $url) {
            $this->actingAs($customer)->get($url)->assertForbidden();
        }
    }

    public function test_admin_can_open_admin_pages(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->actingAs($admin)->get('/admin/pin-sizes')->assertOk();
    }

    public function test_login_redirects_by_role(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'a@example.com']);
        $customer = User::factory()->create(['email' => 'c@example.com']);

        // Admin login lewat /admin/login; tidak bisa lewat /login (pesan sama seperti akun tidak ada)
        $this->post('/admin/login', ['email' => 'a@example.com', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
        auth()->logout();
        $this->from('/login')->post('/login', ['email' => 'a@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        // Pelanggan login lewat /login; tidak bisa lewat /admin/login
        $this->post('/login', ['email' => 'c@example.com', 'password' => 'password'])
            ->assertRedirect(route('customer.orders.index'));
        auth()->logout();
        $this->from('/admin/login')->post('/admin/login', ['email' => 'c@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected(): void
    {
        User::factory()->create(['email' => 'c@example.com']);

        $this->from('/login')->post('/login', ['email' => 'c@example.com', 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
