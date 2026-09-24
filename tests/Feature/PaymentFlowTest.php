<?php

namespace Tests\Feature;

use App\Exceptions\InvalidOrderTransition;
use App\Models\Order;
use App\Models\PinSize;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    private PinSize $size;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        $this->size = PinSize::create(['name' => 'Kecil', 'price' => 5000, 'is_active' => true]);
    }

    private function enableQris(): void
    {
        Storage::disk('public')->put('qris/qris.png', 'x');
        Setting::put('qris_image', 'qris/qris.png');
    }

    private function draftWithItem(User $user, int $qty = 2): Order
    {
        $this->actingAs($user)->post(route('customer.orders.items.store'), [
            'pin_size_id' => $this->size->id,
            'quantity' => $qty,
            'design' => UploadedFile::fake()->image('d.jpg', 100, 100),
        ])->assertSessionHasNoErrors();

        return Order::where('user_id', $user->id)->latest('id')->firstOrFail();
    }

    private function waitingOrder(User $user): Order
    {
        $this->enableQris();
        $order = $this->draftWithItem($user);
        $this->actingAs($user)->post(route('customer.orders.checkout', $order))->assertRedirect();

        return $order->fresh();
    }

    public function test_checkout_moves_draft_to_waiting_payment_and_shows_qris(): void
    {
        $user = User::factory()->create();
        $this->enableQris();
        $order = $this->draftWithItem($user, 3);

        $this->actingAs($user)->post(route('customer.orders.checkout', $order))
            ->assertRedirect(route('customer.orders.show', $order));

        $this->assertSame('menunggu_pembayaran', $order->fresh()->status);
        $this->actingAs($user)->get(route('customer.orders.show', $order))
            ->assertOk()->assertSee('Bayar dengan QRIS')->assertSee('Rp 15.000')->assertSee('storage/qris/qris.png', false);
    }

    public function test_checkout_needs_items_and_qris(): void
    {
        $user = User::factory()->create();
        $order = $this->draftWithItem($user);

        $this->actingAs($user)->post(route('customer.orders.checkout', $order))->assertSessionHasErrors('order');
        $this->assertSame('draft', $order->fresh()->status);

        $this->enableQris();
        $order->items()->delete();
        $order->recalculateTotal();
        $this->actingAs($user)->post(route('customer.orders.checkout', $order))->assertSessionHasErrors('order');
        $this->assertSame('draft', $order->fresh()->status);
    }

    public function test_only_owner_can_use_order_actions(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = $this->waitingOrder($owner);

        foreach (['checkout', 'reopen', 'pay', 'cancel'] as $action) {
            $this->actingAs($other)->post(route("customer.orders.$action", $order))->assertNotFound();
        }
        $this->actingAs($other)->get(route('customer.orders.proof', $order))->assertNotFound();
        $this->assertSame('menunggu_pembayaran', $order->fresh()->status);
    }

    public function test_uploading_proof_moves_to_verification(): void
    {
        $user = User::factory()->create();
        $order = $this->waitingOrder($user);

        $this->actingAs($user)->post(route('customer.orders.pay', $order), [
            'proof' => UploadedFile::fake()->image('bukti.jpg', 300, 300),
        ])->assertRedirect(route('customer.orders.show', $order));

        $order->refresh();
        $this->assertSame('menunggu_verifikasi', $order->status);
        $this->assertNotNull($order->paid_at);
        Storage::disk('local')->assertExists($order->payment_proof);
        $this->actingAs($user)->get(route('customer.orders.proof', $order))->assertOk();
    }

    public function test_proof_validation_and_status_guard(): void
    {
        $user = User::factory()->create();
        $order = $this->waitingOrder($user);

        $this->actingAs($user)->post(route('customer.orders.pay', $order))->assertSessionHasErrors('proof');
        $this->actingAs($user)->post(route('customer.orders.pay', $order), ['proof' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('proof');
        $this->actingAs($user)->post(route('customer.orders.pay', $order), ['proof' => UploadedFile::fake()->image('b.jpg')->size(6000)])
            ->assertSessionHasErrors('proof');
        $this->assertSame('menunggu_pembayaran', $order->fresh()->status);

        // Draft belum boleh menerima bukti
        $draft = $this->draftWithItem(User::factory()->create());
        $this->actingAs($draft->user)->post(route('customer.orders.pay', $draft), ['proof' => UploadedFile::fake()->image('b.jpg')])
            ->assertSessionHasErrors('order');
        $this->assertNull($draft->fresh()->payment_proof);
        $this->assertCount(0, Storage::disk('local')->files('payment_proofs'));
    }

    public function test_reopen_lets_customer_edit_then_checkout_again(): void
    {
        $user = User::factory()->create();
        $order = $this->waitingOrder($user);

        $this->actingAs($user)->post(route('customer.orders.reopen', $order));
        $this->assertSame('draft', $order->fresh()->status);

        $item = $order->items()->first();
        $this->actingAs($user)->delete(route('customer.orders.items.destroy', [$order, $item]))->assertSessionHasNoErrors();
        $this->assertSame(0, $order->fresh()->total);
    }

    public function test_adding_an_item_while_waiting_payment_reopens_the_same_order(): void
    {
        $user = User::factory()->create();
        $order = $this->waitingOrder($user);

        $this->actingAs($user)->post(route('customer.orders.items.store'), [
            'pin_size_id' => $this->size->id,
            'quantity' => 1,
            'design' => UploadedFile::fake()->image('lupa.jpg'),
        ])->assertRedirect(route('customer.orders.show', $order));

        $this->assertSame(1, Order::count());
        $order->refresh();
        $this->assertSame('draft', $order->status);
        $this->assertSame(2, $order->items()->count());
        $this->assertSame(15000, $order->total);
        $this->assertSame(['draft', 'menunggu_pembayaran', 'draft'], $order->statusLogs->pluck('to_status')->all());
    }

    public function test_items_cannot_be_removed_while_waiting_payment(): void
    {
        $user = User::factory()->create();
        $order = $this->waitingOrder($user);

        $this->actingAs($user)->delete(route('customer.orders.items.destroy', [$order, $order->items()->first()]))
            ->assertSessionHasErrors('order');
        $this->assertSame(1, $order->items()->count());
    }

    public function test_customer_can_cancel_only_before_paying(): void
    {
        $user = User::factory()->create();
        $order = $this->waitingOrder($user);
        $this->actingAs($user)->post(route('customer.orders.pay', $order), ['proof' => UploadedFile::fake()->image('b.jpg')]);

        $this->actingAs($user)->post(route('customer.orders.cancel', $order))->assertSessionHasErrors('order');
        $this->assertSame('menunggu_verifikasi', $order->fresh()->status);

        $other = $this->waitingOrder(User::factory()->create());
        $this->actingAs($other->user)->post(route('customer.orders.cancel', $other))->assertSessionHasNoErrors();
        $this->assertSame('dibatalkan', $other->fresh()->status);
    }

    public function test_customer_buttons_only_work_from_their_own_status(): void
    {
        $user = User::factory()->create();
        $order = $this->waitingOrder($user);
        $this->actingAs($user)->post(route('customer.orders.pay', $order), ['proof' => UploadedFile::fake()->image('b.jpg')]);
        $this->assertSame('menunggu_verifikasi', $order->fresh()->status);

        // Setelah bukti dikirim, pelanggan tidak bisa membatalkan, mengubah, checkout ulang, atau kirim bukti lagi.
        foreach (['checkout', 'reopen', 'cancel', 'pay'] as $action) {
            $this->actingAs($user)->post(route("customer.orders.$action", $order), ['proof' => UploadedFile::fake()->image('c.jpg')])
                ->assertSessionHasErrors('order');
            $this->assertSame('menunggu_verifikasi', $order->fresh()->status, $action);
        }

        // Sama saat pesanan sudah diproses admin.
        $order->forceFill(['status' => 'diproses'])->save();
        foreach (['checkout', 'reopen', 'cancel', 'pay'] as $action) {
            $this->actingAs($user)->post(route("customer.orders.$action", $order), ['proof' => UploadedFile::fake()->image('c.jpg')])
                ->assertSessionHasErrors('order');
            $this->assertSame('diproses', $order->fresh()->status, $action);
        }

        // Draft tidak bisa dibatalkan/diubah lewat tombol yang salah.
        $draft = $this->draftWithItem(User::factory()->create());
        foreach (['reopen', 'cancel'] as $action) {
            $this->actingAs($draft->user)->post(route("customer.orders.$action", $draft))->assertSessionHasErrors('order');
            $this->assertSame('draft', $draft->fresh()->status);
        }
    }

    public function test_status_history_is_recorded_with_actor(): void
    {
        $user = User::factory()->create();
        $order = $this->waitingOrder($user);
        $this->actingAs($user)->post(route('customer.orders.pay', $order), ['proof' => UploadedFile::fake()->image('b.jpg')]);

        $logs = $order->fresh()->statusLogs;
        $this->assertSame(['draft', 'menunggu_pembayaran', 'menunggu_verifikasi'], $logs->pluck('to_status')->all());
        $this->assertNull($logs[0]->from_status);
        $this->assertSame('draft', $logs[1]->from_status);
        $this->assertTrue($logs->every(fn ($l) => $l->user_id === $user->id));
    }

    public function test_invalid_transitions_are_rejected_by_the_model(): void
    {
        $user = User::factory()->create();
        $order = $this->draftWithItem($user);

        foreach (['diproses', 'selesai', 'diambil', 'dibatalkan', 'menunggu_verifikasi'] as $to) {
            try {
                $order->transitionTo($to);
                $this->fail("draft -> $to seharusnya ditolak");
            } catch (InvalidOrderTransition $e) {
                $this->assertSame('draft', $order->fresh()->status);
            }
        }

        $order->forceFill(['status' => 'diambil'])->save();
        $this->expectException(InvalidOrderTransition::class);
        $order->transitionTo('draft');
    }

    public function test_whatsapp_url_normalizes_indonesian_numbers(): void
    {
        $cases = ['081234567890' => '6281234567890', '+62 812-3456-7890' => '6281234567890', '81234567890' => '6281234567890', '6281234567890' => '6281234567890'];
        foreach ($cases as $input => $expected) {
            $this->assertSame("https://wa.me/$expected", (new User(['phone' => $input]))->whatsappUrl(), $input);
        }
        $this->assertNull((new User(['phone' => '']))->whatsappUrl());
    }
}
