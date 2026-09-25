<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('public')->put('products/kk.jpg', 'x');
    }

    private function product(string $availability = 'tersedia'): Product
    {
        return Product::create(['image' => 'products/kk.jpg', 'name' => 'Gantungan Kunci', 'price' => 15000, 'availability' => $availability]);
    }

    public function test_order_form_has_no_size_or_design_fields(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $this->actingAs($user)->get(route('customer.orders.create.product', $product))
            ->assertOk()
            ->assertDontSee('pin_size_id', false)
            ->assertDontSee('name="design"', false)
            ->assertSee('quantity', false);
    }

    public function test_ordering_a_product_creates_an_item_without_design_or_pin_size(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $this->actingAs($user)->post(route('customer.orders.products.store', $product), [
            'quantity' => 3, 'notes' => 'bungkus rapi',
        ])->assertRedirect();

        $order = Order::where('user_id', $user->id)->firstOrFail();
        $item = $order->items()->firstOrFail();

        $this->assertSame('draft', $order->status);
        $this->assertSame(45000, $order->total);
        $this->assertSame($product->id, $item->product_id);
        $this->assertNull($item->pin_size_id);
        $this->assertNull($item->design_path);
        $this->assertSame('Gantungan Kunci', $item->size_name);
        $this->assertSame(15000, $item->unit_price);
        $this->assertSame('bungkus rapi', $item->notes);
        $this->assertFalse($item->isCustomPin());
    }

    public function test_cannot_order_unavailable_product(): void
    {
        $user = User::factory()->create();
        $soldOut = $this->product('habis');
        $comingSoon = $this->product('segera');

        $this->actingAs($user)->get(route('customer.orders.create.product', $soldOut))->assertNotFound();
        $this->actingAs($user)->get(route('customer.orders.create.product', $comingSoon))->assertNotFound();
        $this->actingAs($user)->post(route('customer.orders.products.store', $soldOut), ['quantity' => 1])->assertNotFound();

        $this->assertSame(0, OrderItem::count());
    }

    public function test_quantity_validation(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $post = fn ($qty) => $this->actingAs($user)->post(route('customer.orders.products.store', $product), array_filter(['quantity' => $qty], fn ($v) => $v !== null));

        $post(0)->assertSessionHasErrors('quantity');
        $post(1001)->assertSessionHasErrors('quantity');
        $post(null)->assertSessionHasErrors('quantity');
        $this->assertSame(0, OrderItem::count());
    }

    public function test_pin_items_and_product_items_can_share_one_order(): void
    {
        $user = User::factory()->create();
        $size = \App\Models\PinSize::create(['name' => 'Kecil', 'price' => 5000, 'is_active' => true, 'availability' => 'tersedia']);
        $product = $this->product();

        $this->actingAs($user)->post(route('customer.orders.items.store'), [
            'pin_size_id' => $size->id, 'quantity' => 2, 'design' => \Illuminate\Http\UploadedFile::fake()->image('d.jpg'),
        ]);
        $this->actingAs($user)->post(route('customer.orders.products.store', $product), ['quantity' => 1]);

        $this->assertSame(1, Order::count());
        $order = Order::first();
        $this->assertSame(2, $order->items()->count());
        $this->assertSame(25000, $order->total); // 2*5000 + 15000
        $this->assertTrue($order->items->firstWhere('pin_size_id', $size->id)->isCustomPin());
        $this->assertFalse($order->items->firstWhere('product_id', $product->id)->isCustomPin());
    }

    public function test_order_detail_shows_product_photo_for_product_items(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $this->actingAs($user)->post(route('customer.orders.products.store', $product), ['quantity' => 1]);
        $order = Order::first();

        $this->actingAs($user)->get(route('customer.orders.show', $order))
            ->assertOk()
            ->assertSee(asset('storage/products/kk.jpg'), false)
            ->assertSee('Gantungan Kunci');
    }

    public function test_adding_a_product_item_while_waiting_payment_reopens_the_order(): void
    {
        Storage::disk('public')->put('qris/x.png', 'x');
        \App\Models\Setting::put('qris_image', 'qris/x.png');
        $user = User::factory()->create();
        $product = $this->product();

        $this->actingAs($user)->post(route('customer.orders.products.store', $product), ['quantity' => 1]);
        $order = Order::first();
        $this->actingAs($user)->post(route('customer.orders.checkout', $order));
        $this->assertSame('menunggu_pembayaran', $order->fresh()->status);

        $this->actingAs($user)->post(route('customer.orders.products.store', $product), ['quantity' => 2]);
        $this->assertSame(1, Order::count());
        $order->refresh();
        $this->assertSame('draft', $order->status);
        $this->assertSame(2, $order->items()->count());
        $this->assertSame(45000, $order->total);
    }
}
