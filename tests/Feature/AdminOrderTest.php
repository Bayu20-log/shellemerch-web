<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PinSize;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;
    private PinSize $size;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('public')->put('qris/qris.png', 'x');
        Setting::put('qris_image', 'qris/qris.png');

        $this->admin = User::factory()->admin()->create(['name' => 'Admin Toko']);
        $this->customer = User::factory()->create(['name' => 'Budi', 'phone' => '081234567890']);
        $this->size = PinSize::create(['name' => 'Kecil', 'price' => 5000, 'is_active' => true]);
    }

    /** Pesanan pelanggan pada status tertentu, dibuat lewat alur yang sama seperti pelanggan sungguhan. */
    private function orderAt(string $status): Order
    {
        $c = $this->actingAs($this->customer);
        $c->post(route('customer.orders.items.store'), [
            'pin_size_id' => $this->size->id, 'quantity' => 2, 'design' => UploadedFile::fake()->image('d.jpg'),
        ]);
        $order = Order::where('user_id', $this->customer->id)->whereIn('status', ['draft'])->latest('id')->firstOrFail();
        if ($status === 'draft') {
            return $order;
        }
        $c->post(route('customer.orders.checkout', $order));
        if ($status === 'menunggu_pembayaran') {
            return $order->fresh();
        }
        $c->post(route('customer.orders.pay', $order), ['proof' => UploadedFile::fake()->image('b.jpg')]);
        if ($status === 'menunggu_verifikasi') {
            return $order->fresh();
        }
        $this->actingAs($this->admin)->post(route('admin.orders.transition', $order), ['action' => 'confirm']);
        if ($status === 'diproses') {
            return $order->fresh();
        }
        $this->actingAs($this->admin)->post(route('admin.orders.transition', $order), ['action' => 'done']);

        return $order->fresh();
    }

    private function act(Order $order, string $action, ?string $note = null)
    {
        return $this->actingAs($this->admin)->post(route('admin.orders.transition', $order), array_filter(['action' => $action, 'note' => $note]));
    }

    public function test_full_happy_path_from_payment_to_pickup(): void
    {
        $order = $this->orderAt('menunggu_verifikasi');

        $this->act($order, 'confirm')->assertRedirect(route('admin.orders.show', $order));
        $this->assertSame('diproses', $order->fresh()->status);
        $this->act($order, 'done');
        $this->assertSame('selesai', $order->fresh()->status);

        $this->actingAs($this->customer)->get(route('customer.orders.show', $order))
            ->assertOk()->assertSee('siap diambil')->assertSee($order->order_code);

        $this->act($order, 'picked_up');
        $this->assertSame('diambil', $order->fresh()->status);
        $this->assertSame(
            ['draft', 'menunggu_pembayaran', 'menunggu_verifikasi', 'diproses', 'selesai', 'diambil'],
            $order->fresh()->statusLogs->pluck('to_status')->all()
        );
        $this->assertSame($this->admin->id, $order->fresh()->statusLogs->last()->user_id);
    }

    public function test_rejecting_proof_returns_order_to_customer_with_reason_and_allows_reupload(): void
    {
        $order = $this->orderAt('menunggu_verifikasi');
        $oldProof = $order->payment_proof;

        $this->act($order, 'reject')->assertSessionHasErrors('note');
        $this->assertSame('menunggu_verifikasi', $order->fresh()->status);

        $this->act($order, 'reject', 'Nominal kurang Rp 2.000');
        $order->refresh();
        $this->assertSame('menunggu_pembayaran', $order->status);
        $this->assertSame('Nominal kurang Rp 2.000', $order->payment_note);

        $this->actingAs($this->customer)->get(route('customer.orders.show', $order))
            ->assertSee('Bukti pembayaran sebelumnya ditolak')->assertSee('Nominal kurang Rp 2.000');

        $this->actingAs($this->customer)->post(route('customer.orders.pay', $order), ['proof' => UploadedFile::fake()->image('baru.jpg')]);
        $order->refresh();
        $this->assertSame('menunggu_verifikasi', $order->status);
        $this->assertNull($order->payment_note);
        $this->assertNotSame($oldProof, $order->payment_proof);
        Storage::disk('local')->assertMissing($oldProof);
        Storage::disk('local')->assertExists($order->payment_proof);
    }

    public function test_actions_only_work_from_the_right_status(): void
    {
        $waiting = $this->orderAt('menunggu_pembayaran');
        foreach (['confirm', 'reject', 'done', 'picked_up'] as $action) {
            $this->act($waiting, $action, 'x')->assertSessionHasErrors('order');
        }
        $this->assertSame('menunggu_pembayaran', $waiting->fresh()->status);

        $processing = $this->orderAt('diproses');
        $this->act($processing, 'picked_up')->assertSessionHasErrors('order');
        $this->act($processing, 'confirm')->assertSessionHasErrors('order');
        $this->assertSame('diproses', $processing->fresh()->status);
    }

    public function test_cancel_needs_reason_and_is_final(): void
    {
        $order = $this->orderAt('diproses');

        $this->act($order, 'cancel')->assertSessionHasErrors('note');
        $this->act($order, 'cancel', 'Stok bahan habis');
        $this->assertSame('dibatalkan', $order->fresh()->status);

        $this->act($order, 'confirm')->assertSessionHasErrors('order');
        $this->act($order, 'cancel', 'lagi')->assertSessionHasErrors('order');
        $this->actingAs($this->customer)->get(route('customer.orders.show', $order))->assertSee('Stok bahan habis');
    }

    public function test_finished_orders_cannot_be_cancelled(): void
    {
        $order = $this->orderAt('selesai');

        $this->act($order, 'cancel', 'salah')->assertSessionHasErrors('order');
        $this->assertSame('selesai', $order->fresh()->status);
    }

    public function test_unknown_action_is_rejected(): void
    {
        $order = $this->orderAt('menunggu_verifikasi');

        $this->act($order, 'hapus')->assertSessionHasErrors('action');
        $this->assertSame('menunggu_verifikasi', $order->fresh()->status);
    }

    public function test_customer_and_guest_cannot_use_admin_order_pages(): void
    {
        $order = $this->orderAt('menunggu_verifikasi');

        $this->actingAs($this->customer)->get(route('admin.orders.index'))->assertForbidden();
        $this->actingAs($this->customer)->get(route('admin.orders.show', $order))->assertForbidden();
        $this->actingAs($this->customer)->post(route('admin.orders.transition', $order), ['action' => 'confirm'])->assertForbidden();
        $this->actingAs($this->customer)->get(route('admin.payment.edit'))->assertForbidden();
        $this->assertSame('menunggu_verifikasi', $order->fresh()->status);

        auth()->logout();
        $this->get(route('admin.orders.index'))->assertRedirect('/admin/login');
        $this->post(route('admin.orders.transition', $order), ['action' => 'confirm'])->assertRedirect('/admin/login');
    }

    public function test_admin_list_hides_drafts_and_filters_by_status(): void
    {
        // Satu pelanggan hanya punya satu pesanan terbuka, jadi draft dibuat oleh pelanggan lain.
        $other = User::factory()->create();
        $this->actingAs($other)->post(route('customer.orders.items.store'), [
            'pin_size_id' => $this->size->id, 'quantity' => 1, 'design' => UploadedFile::fake()->image('d.jpg'),
        ]);
        $draft = Order::where('user_id', $other->id)->firstOrFail();

        $pending = $this->orderAt('menunggu_verifikasi');
        $done = $this->orderAt('selesai');

        $this->actingAs($this->admin)->get(route('admin.orders.index'))
            ->assertOk()->assertSee($pending->order_code)->assertSee($done->order_code)->assertDontSee($draft->order_code);

        $this->actingAs($this->admin)->get(route('admin.orders.index', ['status' => 'menunggu_verifikasi']))
            ->assertSee($pending->order_code)->assertDontSee($done->order_code);

        $this->actingAs($this->admin)->get(route('admin.orders.show', $draft))->assertNotFound();
        $this->act($draft, 'cancel', 'x')->assertNotFound();
        $this->assertSame('draft', $draft->fresh()->status);
    }

    public function test_admin_detail_shows_customer_proof_and_whatsapp(): void
    {
        $order = $this->orderAt('menunggu_verifikasi');

        $this->actingAs($this->admin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Budi')
            ->assertSee('https://wa.me/6281234567890', false)
            ->assertSee(route('customer.orders.proof', $order), false)
            ->assertSee('Konfirmasi pembayaran')
            ->assertSee('Rp 10.000');
        $this->actingAs($this->admin)->get(route('customer.orders.proof', $order))->assertOk();
    }

    public function test_sidebar_badge_counts_orders_waiting_verification(): void
    {
        $this->orderAt('menunggu_verifikasi');

        $this->actingAs($this->admin)->get(route('admin.orders.index'))
            ->assertSee('Menunggu verifikasi pembayaran');
    }

    public function test_admin_can_set_qris_and_customer_flow_depends_on_it(): void
    {
        Setting::put('qris_image', null);
        $this->assertFalse(Setting::qrisConfigured());

        $this->actingAs($this->admin)->put(route('admin.payment.update'), [])->assertSessionHasErrors('qris');
        $this->actingAs($this->admin)->put(route('admin.payment.update'), ['qris' => UploadedFile::fake()->create('q.pdf', 5, 'application/pdf')])
            ->assertSessionHasErrors('qris');

        $this->actingAs($this->admin)->put(route('admin.payment.update'), [
            'qris' => UploadedFile::fake()->image('qris.png', 300, 300), 'merchant' => 'SHELL E MERCH',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Setting::qrisConfigured());
        $first = Setting::get('qris_image');
        Storage::disk('public')->assertExists($first);

        // Ganti gambar: file lama terhapus; tanpa file baru, gambar lama dipertahankan
        $this->actingAs($this->admin)->put(route('admin.payment.update'), ['qris' => UploadedFile::fake()->image('qris2.png')]);
        Storage::disk('public')->assertMissing($first);
        $second = Setting::get('qris_image');
        $this->actingAs($this->admin)->put(route('admin.payment.update'), ['merchant' => 'TOKO BARU']);
        $this->assertSame($second, Setting::get('qris_image'));
        $this->assertSame('TOKO BARU', Setting::get('qris_merchant'));

        $order = $this->orderAt('menunggu_pembayaran');
        $this->actingAs($this->customer)->get(route('customer.orders.show', $order))->assertSee('Atas nama TOKO BARU');
    }
}
