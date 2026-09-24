@extends('Layout.main')
@section('content')
@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $deltaBadge = function (?int $d) {
        if ($d === null) return '<span class="text-secondary small">tanpa pembanding</span>';
        $cls = $d > 0 ? 'text-success' : ($d < 0 ? 'text-danger' : 'text-secondary');
        return '<span class="small ' . $cls . '">' . ($d > 0 ? '▲ +' : ($d < 0 ? '▼ ' : '')) . $d . '% vs periode sebelumnya</span>';
    };
@endphp
<div class="container-fluid py-4">

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <a href="{{ route('admin.orders.index', ['status' => 'menunggu_verifikasi']) }}" class="text-decoration-none">
                <div class="card h-100 border-warning"><div class="card-body py-3">
                    <div class="small text-secondary">Menunggu verifikasi pembayaran</div>
                    <div class="fs-3 fw-bold text-dark">{{ $pending['menunggu_verifikasi'] ?? 0 }}</div>
                </div></div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.orders.index', ['status' => 'diproses']) }}" class="text-decoration-none">
                <div class="card h-100"><div class="card-body py-3">
                    <div class="small text-secondary">Sedang diproses</div>
                    <div class="fs-3 fw-bold text-dark">{{ $pending['diproses'] ?? 0 }}</div>
                </div></div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.orders.index', ['status' => 'selesai']) }}" class="text-decoration-none">
                <div class="card h-100"><div class="card-body py-3">
                    <div class="small text-secondary">Selesai, belum diambil</div>
                    <div class="fs-3 fw-bold text-dark">{{ $pending['selesai'] ?? 0 }}</div>
                </div></div>
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-2 align-items-end">
                <div class="col-6 col-lg-2">
                    <label class="form-label small mb-1" for="from">Dari</label>
                    <input id="from" type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label small mb-1" for="to">Sampai</label>
                    <input id="to" type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-control form-control-sm">
                </div>
                <div class="col-lg-2"><button type="submit" class="btn btn-sm btn-primary w-100">Tampilkan</button></div>
                <div class="col-lg-6 d-flex flex-wrap gap-1 justify-content-lg-end">
                    @foreach($presets as $d => $p)
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.reports.index', $p) }}">{{ $d == 365 ? '12 bulan' : $d . ' hari' }}</a>
                    @endforeach
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3"><div class="card h-100"><div class="card-body">
            <div class="small text-secondary">Omzet</div>
            <div class="fs-4 fw-bold">{{ $rp($revenue) }}</div>
            {!! $deltaBadge($revenueDelta) !!}
        </div></div></div>
        <div class="col-md-6 col-xl-3"><div class="card h-100"><div class="card-body">
            <div class="small text-secondary">Jumlah pesanan</div>
            <div class="fs-4 fw-bold">{{ number_format($count, 0, ',', '.') }}</div>
            {!! $deltaBadge($countDelta) !!}
        </div></div></div>
        <div class="col-md-6 col-xl-3"><div class="card h-100"><div class="card-body">
            <div class="small text-secondary">Rata-rata per pesanan</div>
            <div class="fs-4 fw-bold">{{ $rp($avg) }}</div>
            <span class="small text-secondary">{{ number_format($pcs, 0, ',', '.') }} pcs pin terjual</span>
        </div></div></div>
        <div class="col-md-6 col-xl-3"><div class="card h-100"><div class="card-body">
            <div class="small text-secondary">Pelanggan</div>
            <div class="fs-4 fw-bold">{{ $customers }}</div>
            <span class="small text-secondary">{{ $repeatCustomers }} memesan lebih dari sekali</span>
        </div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-header pb-0">
            <h6 class="mb-1">Tren {{ $monthly ? 'bulanan' : 'harian' }}</h6>
            <p class="small text-secondary mb-2">Batang = omzet, garis = jumlah pesanan.</p>
        </div>
        <div class="card-body">
            @if($count === 0)
                <p class="text-secondary text-center py-4 mb-0">Belum ada pesanan terkonfirmasi pada rentang ini.</p>
            @else
                <div style="height: 300px;"><canvas id="trendChart" aria-label="Grafik tren omzet dan jumlah pesanan" role="img"></canvas></div>
            @endif
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header pb-0"><h6>Ukuran terlaris</h6></div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th class="ps-3">Ukuran</th><th class="text-end">Pcs</th><th class="text-end">Pesanan</th><th class="text-end pe-3">Omzet</th></tr></thead>
                            <tbody>
                            @forelse($sizes as $s)
                                <tr>
                                    <td class="ps-3">{{ $s->name }}</td>
                                    <td class="text-end">{{ number_format($s->pcs, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ $s->orders }}</td>
                                    <td class="text-end pe-3">{{ $rp($s->revenue) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-secondary py-3">Belum ada data.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header pb-0"><h6>Pelanggan teratas</h6></div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th class="ps-3">Nama</th><th class="text-end">Pesanan</th><th class="text-end pe-3">Omzet</th></tr></thead>
                            <tbody>
                            @forelse($topCustomers as $c)
                                <tr>
                                    <td class="ps-3">{{ $c['name'] }}</td>
                                    <td class="text-end">{{ $c['orders'] }}</td>
                                    <td class="text-end pe-3">{{ $rp($c['revenue']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-secondary py-3">Belum ada data.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <p class="small text-secondary">
        Omzet dihitung dari pesanan yang pembayarannya sudah dikonfirmasi (Diproses, Selesai, Diambil). Pesanan dibatalkan dan yang belum terverifikasi tidak dihitung.
        Tanggal mengikuti saat bukti pembayaran dikirim. Pembanding adalah {{ $days }} hari tepat sebelum rentang ini.
    </p>
</div>

@if($count > 0)
<script>
window.addEventListener('load', function () {
    var el = document.getElementById('trendChart');
    if (!el || typeof Chart === 'undefined') return;
    var d = @json($chart);
    var rp = function (v) { return 'Rp ' + Number(v).toLocaleString('id-ID'); };
    new Chart(el, {
        type: 'bar',
        data: {
            labels: d.labels,
            datasets: [
                { type: 'bar', label: 'Omzet', data: d.revenue, backgroundColor: 'rgba(42,108,162,0.8)', yAxisID: 'rev' },
                { type: 'line', label: 'Jumlah pesanan', data: d.count, borderColor: '#e08a00', backgroundColor: 'transparent', pointRadius: 3, lineTension: 0.2, yAxisID: 'cnt' }
            ]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                xAxes: [{ gridLines: { display: false }, ticks: { maxTicksLimit: 15 } }],
                yAxes: [
                    { id: 'rev', position: 'left', ticks: { beginAtZero: true, callback: function (v) { return rp(v); } } },
                    { id: 'cnt', position: 'right', gridLines: { drawOnChartArea: false }, ticks: { beginAtZero: true, callback: function (v) { return Number.isInteger(v) ? v : ''; } } }
                ]
            },
            tooltips: { callbacks: { label: function (item, data) {
                var ds = data.datasets[item.datasetIndex];
                return ds.label + ': ' + (ds.yAxisID === 'rev' ? rp(item.yLabel) : item.yLabel);
            } } }
        }
    });
});
</script>
@endif
@endsection
