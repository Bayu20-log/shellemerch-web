<?php

namespace Tests\Feature;

use App\Models\PinSize;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    // ---------- Ukuran pin ----------

    public function test_customer_order_form_only_lets_ordering_available_sizes(): void
    {
        $user = User::factory()->create();
        $ok = PinSize::create(['name' => 'Kecil', 'price' => 5000, 'is_active' => true, 'availability' => 'tersedia']);
        $soon = PinSize::create(['name' => 'Edisi Baru', 'price' => 6000, 'is_active' => true, 'availability' => 'segera']);
        $out = PinSize::create(['name' => 'Edisi Lama', 'price' => 4000, 'is_active' => false, 'availability' => 'habis']);

        $response = $this->actingAs($user)->get(route('customer.orders.create'))->assertOk();
        $response->assertSee('Kecil')->assertSee('Edisi Baru')->assertSee('Segera hadir'); // segera tetap tampil sbg pratinjau
        // habis (dan tidak aktif) sama sekali tidak ditampilkan
        $response->assertDontSee('Edisi Lama');

        $post = fn (PinSize $s) => $this->actingAs($user)->post(route('customer.orders.items.store'), [
            'pin_size_id' => $s->id, 'quantity' => 1, 'design' => UploadedFile::fake()->image('d.jpg'),
        ]);
        $post($ok)->assertSessionHasNoErrors();
        $post($soon)->assertSessionHasErrors('pin_size_id');
        $post($out)->assertSessionHasErrors('pin_size_id');
    }

    public function test_admin_toggles_pin_size_availability(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->post(route('admin.pin-sizes.store'), ['name' => 'Kecil', 'price' => 5000, 'availability' => 'tersedia'])->assertSessionHasNoErrors();

        $size = PinSize::firstOrFail();
        $this->assertSame('tersedia', $size->availability);
        $this->assertTrue($size->is_active);

        $this->actingAs($admin)->put(route('admin.pin-sizes.update', $size), ['name' => 'Kecil', 'price' => 5000, 'availability' => 'habis']);
        $size->refresh();
        $this->assertSame('habis', $size->availability);
        $this->assertFalse($size->is_active); // status lama ikut menyesuaikan otomatis

        $this->actingAs($admin)->post(route('admin.pin-sizes.store'), ['name' => 'X', 'price' => 1, 'availability' => 'tidak-valid'])
            ->assertSessionHasErrors('availability');
    }

    // ---------- Produk ----------

    public function test_only_orderable_products_are_listed_for_customer_ordering(): void
    {
        $user = User::factory()->create();
        $ok = Product::create(['image' => 'x.jpg', 'name' => 'Gantungan Kunci', 'price' => 15000, 'availability' => 'tersedia']);
        Product::create(['image' => 'x.jpg', 'name' => 'Stiker', 'price' => 5000, 'availability' => 'habis']);
        Product::create(['image' => 'x.jpg', 'name' => 'Totebag', 'price' => 40000, 'availability' => 'segera']);

        $response = $this->actingAs($user)->get(route('customer.orders.products'))->assertOk();
        $response->assertSee('Gantungan Kunci')->assertDontSee('Stiker')->assertDontSee('Totebag');
        $response->assertSee(route('customer.orders.create.product', $ok), false);
    }

    public function test_ordering_a_specific_product_locks_the_reference_and_blocks_unavailable_ones(): void
    {
        $user = User::factory()->create();
        PinSize::create(['name' => 'Kecil', 'price' => 5000, 'is_active' => true, 'availability' => 'tersedia']);
        $ok = Product::create(['image' => 'x.jpg', 'name' => 'Gantungan Kunci', 'price' => 15000, 'availability' => 'tersedia']);
        $out = Product::create(['image' => 'x.jpg', 'name' => 'Stiker', 'price' => 5000, 'availability' => 'habis']);

        $this->actingAs($user)->get(route('customer.orders.create.product', $ok))
            ->assertOk()->assertSee('Gantungan Kunci')->assertSee('Rp 15.000');

        $this->actingAs($user)->get(route('customer.orders.create.product', $out))->assertNotFound();
    }

    public function test_admin_sets_product_availability_and_it_reflects_publicly(): void
    {
        $admin = User::factory()->admin()->create();
        Storage::disk('public')->put('products/x.jpg', 'x');
        $product = Product::create(['image' => 'products/x.jpg', 'name' => 'Gantungan Kunci', 'price' => 15000, 'availability' => 'tersedia']);

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => 'Gantungan Kunci', 'price' => 15000, 'availability' => 'habis',
        ])->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $this->assertSame('habis', $product->availability);
        $this->get('/products')->assertSee('Habis')->assertDontSee(route('customer.orders.create.product', $product), false);

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => 'Gantungan Kunci', 'price' => 15000, 'availability' => 'ngawur',
        ])->assertSessionHasErrors('availability');
    }

    public function test_admin_product_create_requires_valid_availability(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Baru', 'price' => 1000, 'image' => UploadedFile::fake()->image('p.jpg'),
        ])->assertSessionHasErrors('availability');

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Baru', 'price' => 1000, 'image' => UploadedFile::fake()->image('p.jpg'), 'availability' => 'tersedia',
        ])->assertSessionHasNoErrors();
        $this->assertSame('tersedia', Product::firstOrFail()->availability);
    }
}
