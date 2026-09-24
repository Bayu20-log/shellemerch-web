<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        Carbon::setTestNow('2026-09-25 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function order(User $u, string $status, ?string $paidAt, array $items): Order
    {
        $order = Order::create(['user_id' => $u->id, 'status' => $status]);
        foreach ($items as [$name, $qty, $price]) {
            $order->items()->create(['size_name' => $name, 'unit_price' => $price, 'quantity' => $qty, 'design_path' => 'designs/x.jpg']);
        }
        $order->recalculateTotal();
        $order->forceFill(['paid_at' => $paidAt, 'created_at' => $paidAt ?? '2026-09-10 08:00:00'])->saveQuietly();

        return $order;
    }

    private function report(array $q = [])
    {
        return $this->actingAs($this->admin)->get(route('admin.reports.index', $q));
    }

    public function test_only_confirmed_orders_count_as_revenue(): void
    {
        $u = User::factory()->create();
        $this->order($u, 'diproses', '2026-09-20 10:00:00', [['Kecil', 2, 5000]]);      // 10.000
        $this->order($u, 'selesai', '2026-09-21 10:00:00', [['Kecil', 1, 5000], ['Besar', 1, 8000]]); // 13.000
        $this->order($u, 'diambil', '2026-09-22 10:00:00', [['Besar', 3, 8000]]);       // 24.000
        $this->order($u, 'dibatalkan', '2026-09-22 11:00:00', [['Besar', 99, 8000]]);   // tidak dihitung
        $this->order($u, 'menunggu_verifikasi', '2026-09-23 10:00:00', [['Besar', 99, 8000]]);
        $this->order($u, 'menunggu_pembayaran', null, [['Besar', 99, 8000]]);
        $this->order($u, 'draft', null, [['Besar', 99, 8000]]);

        $r = $this->report()->assertOk();
        $this->assertSame(47000, $r->viewData('revenue'));
        $this->assertSame(3, $r->viewData('count'));
        $this->assertSame(15666, $r->viewData('avg'));
        $this->assertSame(7, $r->viewData('pcs'));   // 2 + (1 + 1) + 3
    }

    public function test_size_breakdown_and_pcs(): void
    {
        $u = User::factory()->create();
        $this->order($u, 'diproses', '2026-09-20 10:00:00', [['Kecil', 2, 5000]]);
        $this->order($u, 'selesai', '2026-09-21 10:00:00', [['Kecil', 1, 5000], ['Besar', 1, 8000]]);
        $this->order($u, 'diambil', '2026-09-22 10:00:00', [['Besar', 3, 8000]]);
        $this->order($u, 'dibatalkan', '2026-09-22 11:00:00', [['Besar', 99, 8000]]);

        $r = $this->report();
        $sizes = $r->viewData('sizes')->keyBy('name');
        $this->assertSame(7, $r->viewData('pcs'));
        $this->assertEquals(4, $sizes['Besar']->pcs);
        $this->assertEquals(32000, $sizes['Besar']->revenue);
        $this->assertEquals(2, $sizes['Besar']->orders);
        $this->assertEquals(3, $sizes['Kecil']->pcs);
        $this->assertEquals(15000, $sizes['Kecil']->revenue);
        $this->assertSame('Besar', $r->viewData('sizes')->first()->name); // terlaris di atas
    }

    public function test_date_range_uses_payment_date_and_previous_period_delta(): void
    {
        $u = User::factory()->create();
        // Periode aktif 10-19 Sep (10 hari); periode sebelumnya 31 Agu-9 Sep
        $this->order($u, 'diproses', '2026-09-10 00:00:00', [['A', 1, 6000]]);
        $this->order($u, 'diproses', '2026-09-19 23:59:59', [['A', 1, 6000]]);
        $this->order($u, 'diproses', '2026-09-20 00:00:00', [['A', 1, 50000]]);  // di luar rentang
        $this->order($u, 'diproses', '2026-09-05 10:00:00', [['A', 1, 6000]]);   // periode sebelumnya
        $this->order($u, 'diproses', '2026-08-31 00:00:00', [['A', 1, 6000]]);   // periode sebelumnya (batas awal)
        $this->order($u, 'diproses', '2026-08-30 23:59:59', [['A', 1, 99000]]);  // sebelum periode sebelumnya

        $r = $this->report(['from' => '2026-09-10', 'to' => '2026-09-19'])->assertOk();
        $this->assertSame(12000, $r->viewData('revenue'));
        $this->assertSame(2, $r->viewData('count'));
        $this->assertSame(10, $r->viewData('days'));
        $this->assertSame(0, $r->viewData('revenueDelta'));   // 12.000 vs 12.000
        $this->assertSame(0, $r->viewData('countDelta'));

        // 11 hari (10-20 Sep): pesanan 20 Sep 00:00 ikut, pembanding menjadi 30 Agu-9 Sep (termasuk 99.000 di 30 Agu 23:59:59)
        $r2 = $this->report(['from' => '2026-09-10', 'to' => '2026-09-20']);
        $this->assertSame(62000, $r2->viewData('revenue'));               // 6.000 + 6.000 + 50.000
        $this->assertSame(-44, $r2->viewData('revenueDelta'));            // (62.000 - 111.000) / 111.000
    }

    public function test_delta_is_null_without_previous_data_and_signed_otherwise(): void
    {
        $u = User::factory()->create();
        $this->order($u, 'diproses', '2026-09-20 10:00:00', [['A', 1, 15000]]);

        $this->assertNull($this->report(['from' => '2026-09-15', 'to' => '2026-09-24'])->viewData('revenueDelta'));

        $this->order($u, 'diproses', '2026-09-10 10:00:00', [['A', 1, 10000]]);   // periode sebelumnya
        $this->assertSame(50, $this->report(['from' => '2026-09-15', 'to' => '2026-09-24'])->viewData('revenueDelta'));  // 15.000 vs 10.000
    }

    public function test_chart_buckets_daily_and_monthly_with_zero_fill(): void
    {
        $u = User::factory()->create();
        $this->order($u, 'diproses', '2026-09-20 10:00:00', [['A', 1, 5000]]);
        $this->order($u, 'diproses', '2026-09-20 15:00:00', [['A', 1, 7000]]);
        $this->order($u, 'diproses', '2026-09-22 10:00:00', [['A', 1, 1000]]);

        $chart = $this->report(['from' => '2026-09-19', 'to' => '2026-09-23'])->viewData('chart');
        $this->assertSame(['19 Sep', '20 Sep', '21 Sep', '22 Sep', '23 Sep'], $chart['labels']);
        $this->assertSame([0, 12000, 0, 1000, 0], $chart['revenue']);
        $this->assertSame([0, 2, 0, 1, 0], $chart['count']);

        $r = $this->report(['from' => '2026-06-01', 'to' => '2026-09-25']);   // > 62 hari: bulanan
        $this->assertTrue($r->viewData('monthly'));
        $chart = $r->viewData('chart');
        $this->assertSame(['Jun 2026', 'Jul 2026', 'Aug 2026', 'Sep 2026'], $chart['labels']);
        $this->assertSame([0, 0, 0, 13000], $chart['revenue']);
    }

    public function test_customers_repeat_and_top_list(): void
    {
        $a = User::factory()->create(['name' => 'Ani']);
        $b = User::factory()->create(['name' => 'Beni']);
        $c = User::factory()->create(['name' => 'Cici']);
        $this->order($a, 'diproses', '2026-09-20 10:00:00', [['A', 1, 5000]]);
        $this->order($a, 'selesai', '2026-09-21 10:00:00', [['A', 1, 5000]]);
        $this->order($b, 'diproses', '2026-09-22 10:00:00', [['A', 1, 30000]]);
        $this->order($c, 'dibatalkan', '2026-09-22 10:00:00', [['A', 1, 90000]]);

        $r = $this->report();
        $this->assertSame(2, $r->viewData('customers'));
        $this->assertSame(1, $r->viewData('repeatCustomers'));
        $this->assertSame(['Beni', 'Ani'], $r->viewData('topCustomers')->pluck('name')->all());
        $this->assertSame(2, $r->viewData('topCustomers')[1]['orders']);
    }

    public function test_pending_snapshot_and_empty_state_and_bad_input(): void
    {
        $u = User::factory()->create();
        $this->order($u, 'menunggu_verifikasi', '2026-09-24 10:00:00', [['A', 1, 5000]]);
        $this->order($u, 'diproses', '2026-01-01 10:00:00', [['A', 1, 5000]]);
        $this->order($u, 'selesai', '2026-01-02 10:00:00', [['A', 1, 5000]]);

        $r = $this->report(['from' => 'ngawur', 'to' => '2026-99-99'])->assertOk();  // default 30 hari
        $this->assertSame(30, $r->viewData('days'));
        $this->assertSame(1, $r->viewData('pending')['menunggu_verifikasi']);
        $this->assertSame(1, $r->viewData('pending')['diproses']);                    // ringkasan tidak terikat rentang tanggal
        $r->assertSee('Belum ada pesanan terkonfirmasi');

        $this->assertSame(731, $this->report(['from' => '2000-01-01', 'to' => '2026-09-25'])->viewData('days'));  // dibatasi
    }

    public function test_admin_only(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.reports.index'))->assertForbidden();
        auth()->logout();
        $this->get(route('admin.reports.index'))->assertRedirect('/login');
    }
}
