<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PinSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function addItem(User $user, PinSize $size, int $qty = 2, array $extra = [])
    {
        return $this->actingAs($user)->post(route('customer.orders.items.store'), array_merge([
            'pin_size_id' => $size->id,
            'quantity' => $qty,
            'design' => UploadedFile::fake()->image('desain.jpg', 200, 200),
        ], $extra));
    }

    public function test_first_item_creates_a_draft_order_with_code_and_total(): void
    {
        $user = User::factory()->create();
        $size = PinSize::create(['name' => 'Kecil', 'price' => 5000, 'is_active' => true]);

        $this->addItem($user, $size, 3, ['notes' => 'warna biru'])->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame('draft', $order->status);
        $this->assertMatchesRegularExpression('/^SM-\d{6}-[A-Z0-9]{4}$/', $order->order_code);
        $this->assertSame(15000, $order->total);

        $item = $order->items()->firstOrFail();
        $this->assertSame('Kecil', $item->size_name);
        $this->assertSame(5000, $item->unit_price);
        $this->assertSame('warna biru', $item->notes);
        Storage::disk('local')->assertExists($item->design_path);
    }

    public function test_more_items_go_into_the_same_draft_and_total_updates(): void
    {
        $user = User::factory()->create();
        $a = PinSize::create(['name' => 'Kecil', 'price' => 5000, 'is_active' => true]);
        $b = PinSize::create(['name' => 'Besar', 'price' => 8000, 'is_active' => true]);

        $this->addItem($user, $a, 2);
        $this->addItem($user, $b, 1);

        $this->assertSame(1, Order::count());
        $this->assertSame(2, OrderItem::count());
        $this->assertSame(18000, Order::first()->total);
    }

    public function test_price_change_does_not_alter_existing_items(): void
    {
        $user = User::factory()->create();
        $size = PinSize::create(['name' => 'Kecil', 'price' => 5000, 'is_active' => true]);
        $this->addItem($user, $size, 2);

        $size->update(['price' => 9999]);

        $this->assertSame(5000, OrderItem::first()->unit_price);
        $this->assertSame(10000, Order::first()->fresh()->total);
    }

    public function test_item_validation_rules(): void
    {
        $user = User::factory()->create();
        $active = PinSize::create(['name' => 'Kecil', 'price' => 5000, 'is_active' => true]);
        $inactive = PinSize::create(['name' => 'Lama', 'price' => 1000, 'is_active' => false]);

        $this->addItem($user, $inactive)->assertSessionHasErrors('pin_size_id');
        $this->addItem($user, $active, 0)->assertSessionHasErrors('quantity');
        $this->addItem($user, $active, 1001)->assertSessionHasErrors('quantity');
        $this->addItem($user, $active, 1, ['design' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('design');
        $this->addItem($user, $active, 1, ['design' => UploadedFile::fake()->image('big.jpg')->size(6000)])
            ->assertSessionHasErrors('design');
        $this->actingAs($user)->post(route('customer.orders.items.store'), ['pin_size_id' => $active->id, 'quantity' => 1])
            ->assertSessionHasErrors('design');

        $this->assertSame(0, Order::count());
    }

    public function test_customer_only_sees_own_orders(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $size = PinSize::create(['name' => 'Kecil', 'price' => 5000, 'is_active' => true]);
        $this->addItem($owner, $size);
        $order = Order::first();
        $item = $order->items()->first();

        $this->actingAs($owner)->get(route('customer.orders.show', $order))->assertOk()->assertSee($order->order_code);
        $this->actingAs($other)->get(route('customer.orders.show', $order))->assertNotFound();
        $this->actingAs($other)->get(route('customer.orders.items.design', [$order, $item]))->assertNotFound();
        $this->actingAs($other)->delete(route('customer.orders.items.destroy', [$order, $item]))->assertNotFound();
        $this->assertSame(1, OrderItem::count());

        $this->actingAs($other)->get(route('customer.orders.index'))->assertOk()->assertDontSee($order->order_code);
    }

    public function test_owner_and_admin_can_view_design_photo(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $size = PinSize::create(['name' => 'Kecil', 'price' => 5000, 'is_active' => true]);
        $this->addItem($owner, $size);
        $order = Order::first();
        $item = $order->items()->first();

        $this->actingAs($owner)->get(route('customer.orders.items.design', [$order, $item]))->assertOk();
        $this->actingAs($admin)->get(route('customer.orders.items.design', [$order, $item]))->assertOk();
        auth()->logout();
        $this->get(route('customer.orders.items.design', [$order, $item]))->assertRedirect('/login');
    }

    public function test_removing_an_item_updates_total_and_deletes_the_file(): void
    {
        $user = User::factory()->create();
        $size = PinSize::create(['name' => 'Kecil', 'price' => 5000, 'is_active' => true]);
        $this->addItem($user, $size, 2);
        $this->addItem($user, $size, 1);
        $order = Order::first();
        $item = $order->items()->orderBy('id')->first();
        $path = $item->design_path;

        $this->actingAs($user)->delete(route('customer.orders.items.destroy', [$order, $item]))->assertRedirect();

        $this->assertSame(1, $order->items()->count());
        $this->assertSame(5000, $order->fresh()->total);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_item_of_another_order_cannot_be_used_through_own_order_url(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $size = PinSize::create(['name' => 'Kecil', 'price' => 5000, 'is_active' => true]);
        $this->addItem($a, $size);
        $this->addItem($b, $size);
        $orderA = Order::where('user_id', $a->id)->first();
        $itemB = Order::where('user_id', $b->id)->first()->items()->first();

        $this->actingAs($a)->delete(route('customer.orders.items.destroy', [$orderA, $itemB]))->assertNotFound();
        $this->actingAs($a)->get(route('customer.orders.items.design', [$orderA, $itemB]))->assertNotFound();
        $this->assertSame(2, OrderItem::count());
    }

    public function test_locked_orders_cannot_be_edited(): void
    {
        $user = User::factory()->create();
        $size = PinSize::create(['name' => 'Kecil', 'price' => 5000, 'is_active' => true]);
        $this->addItem($user, $size);
        $order = Order::first();
        $order->update(['status' => Order::STATUS_PROCESSING]);
        $item = $order->items()->first();

        $this->actingAs($user)->delete(route('customer.orders.items.destroy', [$order, $item]))
            ->assertSessionHasErrors('order');
        $this->assertSame(1, OrderItem::count());
        $this->actingAs($user)->get(route('customer.orders.show', $order))->assertOk()->assertDontSee('Tambah item lagi');
    }

    public function test_new_draft_is_created_after_previous_order_is_no_longer_draft(): void
    {
        $user = User::factory()->create();
        $size = PinSize::create(['name' => 'Kecil', 'price' => 5000, 'is_active' => true]);
        $this->addItem($user, $size);
        Order::first()->update(['status' => Order::STATUS_WAITING_VERIFICATION]);

        $this->addItem($user, $size);

        $this->assertSame(2, Order::count());
        $this->assertSame(1, Order::where('status', 'draft')->count());
    }

    public function test_create_form_lists_only_active_sizes(): void
    {
        $user = User::factory()->create();
        PinSize::create(['name' => 'Ukuran Aktif', 'price' => 5000, 'is_active' => true]);
        PinSize::create(['name' => 'Ukuran Mati', 'price' => 1000, 'is_active' => false]);

        $this->actingAs($user)->get(route('customer.orders.create'))
            ->assertOk()->assertSee('Ukuran Aktif')->assertDontSee('Ukuran Mati');
    }

    public function test_admin_can_manage_pin_sizes_and_customer_cannot(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();

        $this->actingAs($customer)->post(route('admin.pin-sizes.store'), ['name' => 'X', 'price' => 1])->assertForbidden();
        $this->assertSame(0, PinSize::count());

        $this->actingAs($admin)->post(route('admin.pin-sizes.store'), ['name' => 'Kecil', 'price' => 5000, 'is_active' => 1])->assertRedirect();
        $size = PinSize::firstOrFail();
        $this->assertTrue($size->is_active);

        $this->actingAs($admin)->put(route('admin.pin-sizes.update', $size), ['name' => 'Kecil+', 'price' => 6000, 'is_active' => 0]);
        $size->refresh();
        $this->assertSame(6000, $size->price);
        $this->assertFalse($size->is_active);

        $this->actingAs($admin)->post(route('admin.pin-sizes.store'), ['name' => '', 'price' => -5])->assertSessionHasErrors(['name', 'price']);

        $this->actingAs($admin)->delete(route('admin.pin-sizes.destroy', $size));
        $this->assertSame(0, PinSize::count());
    }

    public function test_deleting_a_size_keeps_old_order_items(): void
    {
        $user = User::factory()->create();
        $size = PinSize::create(['name' => 'Kecil', 'price' => 5000, 'is_active' => true]);
        $this->addItem($user, $size, 2);

        $size->delete();

        $item = OrderItem::firstOrFail();
        $this->assertNull($item->pin_size_id);
        $this->assertSame('Kecil', $item->size_name);
        $this->assertSame(10000, Order::first()->total);
    }
}
