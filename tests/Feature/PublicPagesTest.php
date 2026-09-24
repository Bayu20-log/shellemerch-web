<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = ['/', '/products', '/about', '/news'];

    public function test_public_pages_show_login_link_to_guests(): void
    {
        foreach (self::PAGES as $url) {
            $this->get($url)->assertOk()->assertSee(route('login'), false);
        }
    }

    public function test_customer_sees_orders_link_and_no_login_button(): void
    {
        $customer = User::factory()->create();

        foreach (self::PAGES as $url) {
            $this->actingAs($customer)->get($url)->assertOk()
                ->assertSee('Pesanan Saya')
                ->assertDontSee('>Masuk<', false);
        }
    }

    public function test_navbar_never_links_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();

        foreach (self::PAGES as $url) {
            $this->get($url)->assertOk()->assertDontSee('/admin');
            $this->actingAs($customer)->get($url)->assertOk()->assertDontSee('/admin');
            $this->actingAs($admin)->get($url)->assertOk()
                ->assertDontSee('/admin')
                ->assertDontSee('Pesanan Saya')
                ->assertDontSee('>Masuk<', false);
        }
    }

    public function test_admin_entry_point_is_slash_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->get('/admin')->assertRedirect('/admin/dashboard');
    }

    public function test_product_button_goes_to_order_form_with_reference(): void
    {
        $product = Product::create(['image' => 'x.jpg', 'name' => 'Pin Enamel', 'price' => 10000, 'description' => 'tes']);

        $this->get('/products')->assertOk()
            ->assertSee(route('customer.orders.create', ['product' => $product->id]), false);

        $this->get(route('customer.orders.create', ['product' => $product->id]))->assertRedirect('/login');
    }
}
