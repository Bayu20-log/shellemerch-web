<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use ZipArchive;

class AdminOrderListTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    private function order(User $user, string $status, string $createdAt, array $items = [['Kecil', 2, 5000]], ?string $paidAt = null): Order
    {
        $order = Order::create(['user_id' => $user->id, 'status' => $status]);
        foreach ($items as [$name, $qty, $price]) {
            $order->items()->create(['size_name' => $name, 'unit_price' => $price, 'quantity' => $qty, 'design_path' => 'designs/x.jpg']);
        }
        $order->recalculateTotal();
        $order->forceFill(['created_at' => $createdAt, 'paid_at' => $paidAt])->saveQuietly();

        return $order->fresh();
    }

    private function list(array $query = [])
    {
        return $this->actingAs($this->admin)->get(route('admin.orders.index', $query));
    }

    public function test_search_by_code_name_email_and_phone(): void
    {
        $budi = User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@toko.id', 'phone' => '081311112222']);
        $sari = User::factory()->create(['name' => 'Sari Dewi', 'email' => 'sari@toko.id', 'phone' => '085599990000']);
        $ob = $this->order($budi, 'diproses', '2026-09-01 10:00:00');
        $os = $this->order($sari, 'diproses', '2026-09-02 10:00:00');

        foreach ([$ob->order_code => $ob, 'santoso' => $ob, 'budi@toko' => $ob, '0813111' => $ob, 'dewi' => $os, '08559999' => $os] as $q => $expected) {
            $other = $expected->is($ob) ? $os : $ob;
            $this->list(['q' => $q])->assertOk()->assertSee($expected->order_code)->assertDontSee($other->order_code);
        }
        $this->list(['q' => 'tidak-ada-orang-ini'])->assertSee('Tidak ada pesanan yang cocok');
    }

    public function test_date_range_filter_is_inclusive_and_tolerates_bad_input(): void
    {
        $u = User::factory()->create();
        $a = $this->order($u, 'diproses', '2026-09-01 00:00:00');
        $b = $this->order($u, 'diproses', '2026-09-05 23:59:59');
        $c = $this->order($u, 'diproses', '2026-09-06 00:00:00');

        $this->list(['from' => '2026-09-01', 'to' => '2026-09-05'])
            ->assertSee($a->order_code)->assertSee($b->order_code)->assertDontSee($c->order_code);
        $this->list(['from' => '2026-09-06'])->assertSee($c->order_code)->assertDontSee($a->order_code);
        // tanggal terbalik ditukar; input ngawur diabaikan tanpa error
        $this->list(['from' => '2026-09-05', 'to' => '2026-09-01'])->assertSee($a->order_code)->assertDontSee($c->order_code);
        $this->list(['from' => 'bukan-tanggal', 'to' => '2026-13-45', 'sort' => 'hack', 'dir' => 'x', 'status' => ['a']])
            ->assertOk()->assertSee($a->order_code)->assertSee($c->order_code);
    }

    public function test_sorting(): void
    {
        $u = User::factory()->create(['name' => 'Zed']);
        $v = User::factory()->create(['name' => 'Adi']);
        $small = $this->order($u, 'diproses', '2026-09-01 10:00:00', [['A', 1, 1000]]);
        $big = $this->order($v, 'diproses', '2026-09-02 10:00:00', [['A', 10, 9000]]);

        $asc = fn (array $q) => $this->list($q)->assertOk()->viewData('orders')->pluck('id')->all();
        $this->assertSame([$small->id, $big->id], $asc(['sort' => 'total', 'dir' => 'asc']));
        $this->assertSame([$big->id, $small->id], $asc(['sort' => 'total', 'dir' => 'desc']));
        $this->assertSame([$big->id, $small->id], $asc(['sort' => 'customer', 'dir' => 'asc']));   // Adi dulu
        $this->assertSame([$big->id, $small->id], $asc(['sort' => 'pcs', 'dir' => 'desc']));
        $this->assertSame([$big->id, $small->id], $asc([]));                                        // bawaan: terbaru dulu
    }

    public function test_pagination_and_filters_survive_page_links(): void
    {
        $u = User::factory()->create();
        for ($i = 0; $i < 30; $i++) {
            $this->order($u, 'diproses', '2026-09-01 10:00:00');
        }

        $p1 = $this->list(['status' => 'diproses']);
        $this->assertCount(25, $p1->viewData('orders'));
        $p1->assertSee('status=diproses', false)->assertSee('page=2', false)->assertSee('Menampilkan 1-25 dari 30');
        $this->assertCount(5, $this->list(['status' => 'diproses', 'page' => 2])->viewData('orders'));
    }

    public function test_summary_excludes_cancelled_and_drafts(): void
    {
        $u = User::factory()->create();
        $this->order($u, 'diproses', '2026-09-01 10:00:00', [['A', 2, 5000]]);   // 10.000, 2 pcs
        $this->order($u, 'selesai', '2026-09-02 10:00:00', [['A', 1, 7000]]);    // 7.000, 1 pcs
        $this->order($u, 'dibatalkan', '2026-09-03 10:00:00', [['A', 9, 9000]]); // dihitung sebagai pesanan, bukan nilai
        $this->order($u, 'draft', '2026-09-04 10:00:00', [['A', 5, 5000]]);      // tidak muncul sama sekali

        $r = $this->list();
        $this->assertSame(['count' => 3, 'value' => 17000, 'pcs' => 3], $r->viewData('summary'));
        $r->assertSee('Rp 17.000');
        $this->assertSame(1, $r->viewData('counts')['dibatalkan']);
        $this->assertArrayNotHasKey('draft', $r->viewData('counts')->all());
    }

    public function test_admin_only(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('admin.orders.export'))->assertForbidden();
        auth()->logout();
        $this->get(route('admin.orders.export'))->assertRedirect('/login');
    }

    public function test_export_downloads_xlsx_respecting_filters_without_drafts(): void
    {
        $budi = User::factory()->create(['name' => 'Budi & <Co>', 'email' => 'budi@toko.id', 'phone' => '081311112222']);
        $sari = User::factory()->create(['name' => 'Sari']);
        $o1 = $this->order($budi, 'diproses', '2026-09-01 10:30:00', [['Kecil', 2, 5000], ['Besar', 1, 8000]], '2026-09-01 11:00:00');
        $o2 = $this->order($sari, 'selesai', '2026-09-20 09:00:00');
        $draft = $this->order($budi, 'draft', '2026-09-02 10:00:00');

        $response = $this->actingAs($this->admin)->get(route('admin.orders.export', ['from' => '2026-09-01', 'to' => '2026-09-10']));
        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertMatchesRegularExpression('/pesanan-shellemerch-\d{8}-\d{6}\.xlsx/', $response->headers->get('Content-Disposition'));

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($response->baseResponse->getFile()->getPathname()));
        $orders = $zip->getFromName('xl/worksheets/sheet1.xml');
        $items = $zip->getFromName('xl/worksheets/sheet2.xml');
        $info = $zip->getFromName('xl/worksheets/sheet3.xml');
        $zip->close();

        $this->assertStringContainsString($o1->order_code, $orders);
        $this->assertStringContainsString('Budi &amp; &lt;Co&gt;', $orders);
        $this->assertStringContainsString('081311112222', $orders);
        $this->assertStringContainsString('<v>18000</v>', $orders);              // total 2*5000 + 8000
        $this->assertStringNotContainsString($o2->order_code, $orders);          // di luar rentang tanggal
        $this->assertStringNotContainsString($draft->order_code, $orders);       // draft tidak pernah ikut
        $this->assertStringContainsString('Kecil', $items);
        $this->assertStringContainsString('Besar', $items);
        $this->assertSame(2, substr_count($items, $o1->order_code));             // satu baris per item
        $this->assertStringContainsString('2026-09-01', $info);
        $this->assertStringContainsString('<autoFilter ref="A1:J2"/>', $orders); // 1 pesanan cocok filter
    }
}
