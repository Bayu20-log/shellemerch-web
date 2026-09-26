<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PinSize;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StockTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('public')->put('products/p.jpg', 'x');
    }

    private function product(int $stock, string $availability = 'tersedia'): Product
    {
        return Product::create(['image' => 'products/p.jpg', 'name' => 'Gantungan Kunci', 'price' => 10000, 'availability' => $availability, 'stock' => $stock]);
    }

    private function pinSize(int $stock, string $availability = 'tersedia'): PinSize
    {
        return PinSize::create(['name' => 'Kecil', 'price' => 5000, 'is_active' => true, 'availability' => $availability, 'stock' => $stock]);
    }

    // ---------- Admin: stok 0 memaksa status habis ----------

    public function test_admin_saving_stock_zero_forces_sold_out_regardless_of_chosen_status(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.pin-sizes.store'), [
            'name' => 'Kecil', 'price' => 5000, 'availability' => 'tersedia', 'stock' => 0,
        ]);
        $size = PinSize::firstOrFail();
        $this->assertSame('habis', $size->availability);
        $this->assertSame(0, $size->stock);

        $this->actingAs($admin)->put(route('admin.products.update', $this->product(5)), [
            'name' => 'Gantungan Kunci', 'price' => 10000, 'availability' => 'tersedia', 'stock' => 0,
        ]);
        $product = Product::firstOrFail();
        $this->assertSame('habis', $product->availability);
    }

    public function test_admin_leaving_stock_blank_means_unlimited_and_untracked(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.pin-sizes.store'), [
            'name' => 'Kecil', 'price' => 5000, 'availability' => 'tersedia',
        ]);
        $size = PinSize::firstOrFail();
        $this->assertNull($size->stock);
        $this->assertTrue($size->isOrderable());
        $this->assertFalse($size->tracksStock());
    }

    // ---------- Pin custom ----------

    public function test_ordering_a_tracked_pin_size_decrements_its_stock(): void
    {
        $user = User::factory()->create();
        $size = $this->pinSize(5);

        $this->actingAs($user)->post(route('customer.orders.items.store'), [
            'pin_size_id' => $size->id, 'quantity' => 2, 'design' => UploadedFile::fake()->image('d.jpg'),
        ])->assertSessionHasNoErrors();

        $this->assertSame(3, $size->fresh()->stock);
        $this->assertSame('tersedia', $size->fresh()->availability);
    }

    public function test_ordering_exactly_the_remaining_pin_stock_marks_it_sold_out(): void
    {
        $user = User::factory()->create();
        $size = $this->pinSize(2);

        $this->actingAs($user)->post(route('customer.orders.items.store'), [
            'pin_size_id' => $size->id, 'quantity' => 2, 'design' => UploadedFile::fake()->image('d.jpg'),
        ])->assertSessionHasNoErrors();

        $size->refresh();
        $this->assertSame(0, $size->stock);
        $this->assertSame('habis', $size->availability);

        // Ukuran yang sudah habis tetap tampil (seperti "segera hadir") tapi tidak bisa dipilih/dipesan lagi.
        $this->actingAs($user)->get(route('customer.orders.create'))->assertSee('Kecil')->assertSee('Habis');
        $this->actingAs($user)->post(route('customer.orders.items.store'), [
            'pin_size_id' => $size->id, 'quantity' => 1, 'design' => UploadedFile::fake()->image('d2.jpg'),
        ])->assertSessionHasErrors('pin_size_id');
    }

    public function test_ordering_more_than_available_pin_stock_is_rejected(): void
    {
        $user = User::factory()->create();
        $size = $this->pinSize(3);

        $this->actingAs($user)->post(route('customer.orders.items.store'), [
            'pin_size_id' => $size->id, 'quantity' => 4, 'design' => UploadedFile::fake()->image('d.jpg'),
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(3, $size->fresh()->stock);
        $this->assertSame(0, \App\Models\OrderItem::count());
    }

    public function test_deleting_a_pin_item_restores_its_stock(): void
    {
        $user = User::factory()->create();
        $size = $this->pinSize(5);

        $this->actingAs($user)->post(route('customer.orders.items.store'), [
            'pin_size_id' => $size->id, 'quantity' => 2, 'design' => UploadedFile::fake()->image('d.jpg'),
        ]);
        $this->assertSame(3, $size->fresh()->stock);

        $order = Order::first();
        $item = $order->items()->first();
        $this->actingAs($user)->delete(route('customer.orders.items.destroy', [$order, $item]));

        $this->assertSame(5, $size->fresh()->stock);
    }

    public function test_cancelling_an_order_restores_pin_stock_and_reopens_sold_out_size(): void
    {
        $user = User::factory()->create();
        Storage::disk('public')->put('qris/x.png', 'x');
        \App\Models\Setting::put('qris_image', 'qris/x.png');
        $size = $this->pinSize(2);

        $this->actingAs($user)->post(route('customer.orders.items.store'), [
            'pin_size_id' => $size->id, 'quantity' => 2, 'design' => UploadedFile::fake()->image('d.jpg'),
        ]);
        $this->assertSame('habis', $size->fresh()->availability);

        $order = Order::first();
        $this->actingAs($user)->post(route('customer.orders.checkout', $order));
        $this->actingAs($user)->post(route('customer.orders.cancel', $order));

        $size->refresh();
        $this->assertSame(2, $size->stock);
        $this->assertSame('tersedia', $size->availability); // otomatis pulih karena sebelumnya habis akibat stok
    }

    // ---------- Produk katalog ----------

    public function test_ordering_a_tracked_product_decrements_its_stock_and_hits_zero(): void
    {
        $user = User::factory()->create();
        $product = $this->product(3);

        $this->actingAs($user)->post(route('customer.orders.products.store', $product), ['quantity' => 3])
            ->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertSame(0, $product->stock);
        $this->assertSame('habis', $product->availability);
        $this->actingAs($user)->get(route('customer.orders.create.product', $product))->assertNotFound();
    }

    public function test_ordering_more_than_available_product_stock_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = $this->product(2);

        $this->actingAs($user)->post(route('customer.orders.products.store', $product), ['quantity' => 5])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_deleting_a_product_item_restores_its_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->product(4);

        $this->actingAs($user)->post(route('customer.orders.products.store', $product), ['quantity' => 3]);
        $this->assertSame(1, $product->fresh()->stock);

        $order = Order::first();
        $item = $order->items()->first();
        $this->actingAs($user)->delete(route('customer.orders.items.destroy', [$order, $item]));

        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_admin_cancelling_a_paid_order_restores_stock_for_both_kinds_of_items(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        Storage::disk('public')->put('qris/x.png', 'x');
        \App\Models\Setting::put('qris_image', 'qris/x.png');
        $size = $this->pinSize(5);
        $product = $this->product(5);

        $this->actingAs($user)->post(route('customer.orders.items.store'), [
            'pin_size_id' => $size->id, 'quantity' => 2, 'design' => UploadedFile::fake()->image('d.jpg'),
        ]);
        $order = Order::first();
        // tambahkan item produk ke pesanan yang sama lewat draft yang berjalan
        $this->actingAs($user)->post(route('customer.orders.products.store', $product), ['quantity' => 3]);

        $this->assertSame(3, $size->fresh()->stock);
        $this->assertSame(2, $product->fresh()->stock);

        $this->actingAs($user)->post(route('customer.orders.checkout', $order));
        $this->actingAs($user)->post(route('customer.orders.pay', $order), ['proof' => UploadedFile::fake()->image('b.jpg')]);

        $this->actingAs($admin)->post(route('admin.orders.transition', $order), ['action' => 'cancel', 'note' => 'stok salah hitung']);

        $this->assertSame(5, $size->fresh()->stock);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_untracked_stock_is_unaffected_by_ordering(): void
    {
        $user = User::factory()->create();
        $size = $this->pinSize(0); // placeholder, will override to null below
        $size->update(['stock' => null, 'availability' => 'tersedia']);

        $this->actingAs($user)->post(route('customer.orders.items.store'), [
            'pin_size_id' => $size->id, 'quantity' => 500, 'design' => UploadedFile::fake()->image('d.jpg'),
        ])->assertSessionHasNoErrors();

        $this->assertNull($size->fresh()->stock);
        $this->assertSame('tersedia', $size->fresh()->availability);
    }
}
