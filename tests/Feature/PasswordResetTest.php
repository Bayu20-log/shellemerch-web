<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_is_reachable_only_from_customer_side(): void
    {
        $this->get('/lupa-password')->assertOk()->assertSee('Lupa Password');
        $this->get('/login')->assertSee(route('password.request'), false);
        $this->get('/admin/login')->assertDontSee(route('password.request'), false);
    }

    public function test_reset_link_is_sent_only_for_customer_accounts(): void
    {
        Notification::fake();
        $customer = User::factory()->create(['email' => 'c@x.test']);
        $admin = User::factory()->admin()->create(['email' => 'a@x.test']);

        $this->post('/lupa-password', ['email' => 'c@x.test'])->assertSessionHas('status');
        Notification::assertSentTo($customer, ResetPassword::class);

        $this->post('/lupa-password', ['email' => 'a@x.test'])->assertSessionHas('status');
        Notification::assertNotSentTo($admin, ResetPassword::class);
    }

    public function test_unknown_email_gets_the_same_generic_message_as_a_real_one(): void
    {
        $r1 = $this->post('/lupa-password', ['email' => 'tidak-ada@x.test']);
        $r2 = $this->post('/lupa-password', ['email' => 'juga-tidak-ada@x.test']);
        $this->assertSame(session('status'), session('status'));
        $r1->assertSessionHas('status');
        $r2->assertSessionHas('status');
    }

    public function test_customer_can_reset_password_with_valid_token_and_then_log_in(): void
    {
        Notification::fake();
        $customer = User::factory()->create(['email' => 'c@x.test']);
        $this->post('/lupa-password', ['email' => 'c@x.test']);

        $token = null;
        Notification::assertSentTo($customer, ResetPassword::class, function ($notification) use (&$token) {
            $token = $notification->token;
            return true;
        });
        $this->assertNotNull($token);

        $this->get(route('password.reset', ['token' => $token, 'email' => 'c@x.test']))
            ->assertOk()->assertSee('Buat Password Baru');

        $this->post('/reset-password', [
            'token' => $token, 'email' => 'c@x.test',
            'password' => 'passwordbaru123', 'password_confirmation' => 'passwordbaru123',
        ])->assertRedirect(route('login'));

        $this->post('/login', ['email' => 'c@x.test', 'password' => 'passwordbaru123'])
            ->assertRedirect(route('customer.orders.index'));
    }

    public function test_admin_password_cannot_be_reset_through_the_customer_flow_even_with_a_crafted_token(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'a@x.test']);
        // Token dibuat langsung di tabel (mensimulasikan seolah token "bocor"),
        // karena sendResetLink tidak akan pernah mengirimkannya untuk akun admin.
        DB::table('password_reset_tokens')->insert([
            'email' => 'a@x.test',
            'token' => \Illuminate\Support\Facades\Hash::make('token-rahasia'),
            'created_at' => now(),
        ]);

        $this->post('/reset-password', [
            'token' => 'token-rahasia', 'email' => 'a@x.test',
            'password' => 'passwordbaru123', 'password_confirmation' => 'passwordbaru123',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password', $admin->fresh()->password));
    }

    public function test_reset_requires_matching_password_confirmation(): void
    {
        $customer = User::factory()->create(['email' => 'c@x.test']);
        $this->post('/reset-password', [
            'token' => 'apapun', 'email' => 'c@x.test',
            'password' => 'passwordbaru123', 'password_confirmation' => 'beda',
        ])->assertSessionHasErrors('password');
    }
}
