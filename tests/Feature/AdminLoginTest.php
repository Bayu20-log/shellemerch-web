<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_log_in_through_customer_form_and_vice_versa(): void
    {
        User::factory()->admin()->create(['email' => 'a@x.test']);
        User::factory()->create(['email' => 'c@x.test']);

        $this->from('/login')->post('/login', ['email' => 'a@x.test', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->from('/admin/login')->post('/admin/login', ['email' => 'c@x.test', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_correct_form_logs_in_and_redirects_to_the_right_home(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'a@x.test']);
        $customer = User::factory()->create(['email' => 'c@x.test']);

        $this->post('/admin/login', ['email' => 'a@x.test', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
        auth()->logout();

        $this->post('/login', ['email' => 'c@x.test', 'password' => 'password'])
            ->assertRedirect(route('customer.orders.index'));
        $this->assertAuthenticatedAs($customer);
    }

    public function test_admin_registration_page_does_not_exist(): void
    {
        $this->get('/admin/login')->assertOk()->assertDontSee(route('register'));
    }

    public function test_already_logged_in_users_are_redirected_away_from_either_login_form(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/login')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($admin)->get('/admin/login')->assertRedirect(route('admin.dashboard'));

        $customer = User::factory()->create();
        $this->actingAs($customer)->get('/login')->assertRedirect(route('customer.orders.index'));
        $this->actingAs($customer)->get('/admin/login')->assertRedirect(route('customer.orders.index'));
    }

    public function test_logout_sends_admin_and_customer_to_their_own_login(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->post('/logout')->assertRedirect(route('admin.login'));

        $customer = User::factory()->create();
        $this->actingAs($customer)->post('/logout')->assertRedirect('/');
    }

    public function test_login_attempts_are_still_throttled(): void
    {
        User::factory()->admin()->create(['email' => 'a@x.test']);
        for ($i = 0; $i < 6; $i++) {
            $this->post('/admin/login', ['email' => 'a@x.test', 'password' => 'salah']);
        }
        $this->post('/admin/login', ['email' => 'a@x.test', 'password' => 'salah'])->assertStatus(429);
    }
}
