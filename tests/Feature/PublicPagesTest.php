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

    public function test_public_pages_show_orders_link_to_customers_and_dashboard_to_admins(): void
    {
        $customer = User::factory()->create();
        $admin = User::factory()->admin()->create();

        foreach (self::PAGES as $url) {
            $this->actingAs($customer)->get($url)->assertOk()->assertSee('Pesanan Saya');
            $this->actingAs($admin)->get($url)->assertOk()->assertSee('Dashboard');
        }
    }

    public function test_product_button_goes_to_order_form_with_reference(): void
    {
        $product = Product::create(['image' => 'x.jpg', 'name' => 'Pin Enamel', 'price' => 10000, 'description' => 'tes']);

        $this->get('/products')->assertOk()
            ->assertSee(route('customer.orders.create', ['product' => $product->id]), false);

        $this->get(route('customer.orders.create', ['product' => $product->id]))->assertRedirect('/login');
    }
}
