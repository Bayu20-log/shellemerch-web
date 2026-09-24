<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ], $override);
    }

    public function test_register_page_is_reachable(): void
    {
        $this->get('/daftar')->assertOk()->assertSee('Buat Akun');
    }

    public function test_customer_can_register_and_is_logged_in(): void
    {
        $this->post('/daftar', $this->payload())->assertRedirect(route('customer.orders.index'));

        $user = User::where('email', 'budi@example.com')->firstOrFail();
        $this->assertSame('customer', $user->role);
        $this->assertSame('081234567890', $user->phone);
        $this->assertAuthenticatedAs($user);
    }

    public function test_role_cannot_be_injected_through_registration(): void
    {
        $this->post('/daftar', $this->payload(['role' => 'admin', 'is_admin' => 1]));

        $user = User::where('email', 'budi@example.com')->firstOrFail();
        $this->assertSame('customer', $user->role);
        $this->assertFalse($user->isAdmin());
        $this->get('/admin/dashboard')->assertForbidden();
    }

    public function test_registration_validation(): void
    {
        User::factory()->create(['email' => 'budi@example.com']);

        $this->post('/daftar', $this->payload())->assertSessionHasErrors('email');
        $this->post('/daftar', $this->payload(['email' => 'baru@example.com', 'password' => 'pendek', 'password_confirmation' => 'pendek']))
            ->assertSessionHasErrors('password');
        $this->post('/daftar', $this->payload(['email' => 'baru@example.com', 'password_confirmation' => 'beda']))
            ->assertSessionHasErrors('password');
        $this->post('/daftar', $this->payload(['email' => 'baru@example.com', 'phone' => 'abc']))
            ->assertSessionHasErrors('phone');
    }
}
