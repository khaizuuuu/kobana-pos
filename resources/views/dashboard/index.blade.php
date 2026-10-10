@extends('layouts.app')

@section('title', 'Dashboard Analitik')
@section('page-title', 'Dashboard Analitik Penjualan')

@section('content')

{{-- â”€â”€â”€ Filter Rentang Tanggal â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
<form method="GET" action="{{ route('dashboard') }}" class="card shadow-sm mb-4 p-3">
    <div class="row g-2 align-items-end">
        <div class="col-sm-4">
            <label class="form-label fw-semibold small mb-1">Dari Tanggal</label>
            <input type="date" name="date_from" class="form-control form-control-sm"
                   value="{{ $dateFrom->toDateString() }}">
        </div>
        <div class="col-sm-4">
            <label class="form-label fw-semibold small mb-1">Sampai Tanggal</label>
            <input type="date" name="date_to" class="form-control form-control-sm"
                   value="{{ $dateTo->toDateString() }}">
        </div>
        <div class="col-sm-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                <i class="bi bi-funnel me-1"></i>Terapkan
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </div>
</form>

{{-- â”€â”€â”€ Kartu Ringkasan (8 kartu) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
<div class="row g-3 mb-4">

    {{-- Omzet Periode --}}
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card shadow-sm h-100 p-3 border-start border-danger border-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-danger bg-opacity-10 p-2 text-danger">
                    <i class="bi bi-wallet2 fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Omzet Periode</div>
                    <div class="fw-bold fs-6 text-dark">@rupiah($stats['total_omzet'])</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Transaksi --}}
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card shadow-sm h-100 p-3 border-start border-primary border-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 p-2 text-primary">
                    <i class="bi bi-receipt-cutoff fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Transaksi</div>
                    <div class="fw-bold fs-6 text-dark">{{ number_format($stats['total_transactions'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Item Terjual --}}
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card shadow-sm h-100 p-3 border-start border-warning border-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning bg-opacity-10 p-2 text-warning">
                    <i class="bi bi-box-seam-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Item Terjual</div>
                    <div class="fw-bold fs-6 text-dark">{{ number_format($stats['total_items'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Rata-rata Transaksi --}}
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card shadow-sm h-100 p-3 border-start border-success border-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success bg-opacity-10 p-2 text-success">
                    <i class="bi bi-graph-up-arrow fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Rata-rata Trx</div>
                    <div class="fw-bold fs-6 text-dark">@rupiah($stats['avg_transaction'])</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Omzet Hari Ini --}}
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card shadow-sm h-100 p-3 border-start border-info border-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info bg-opacity-10 p-2 text-info">
                    <i class="bi bi-sun fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Omzet Hari Ini</div>
                    <div class="fw-bold fs-6 text-dark">@rupiah($stats['omzet_today'])</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Transaksi Hari Ini --}}
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card shadow-sm h-100 p-3 border-start border-secondary border-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-secondary bg-opacity-10 p-2 text-secondary">
                    <i class="bi bi-calendar-day fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Trx Hari Ini</div>
                    <div class="fw-bold fs-6 text-dark">{{ number_format($stats['trx_today'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Menu --}}
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card shadow-sm h-100 p-3 border-start border-orange border-4" style="border-color: #fd7e14 !important;">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-2" style="background:rgba(253,126,20,0.12); color:#fd7e14;">
                    <i class="bi bi-cup-hot-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Menu</div>
                    <div class="fw-bold fs-6 text-dark">{{ number_format($stats['total_menu'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Kategori --}}
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card shadow-sm h-100 p-3 border-start border-purple border-4" style="border-color: #6f42c1 !important;">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-2" style="background:rgba(111,66,193,0.12); color:#6f42c1;">
                    <i class="bi bi-tags-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Kategori</div>
                    <div class="fw-bold fs-6 text-dark">{{ number_format($stats['total_category'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- â”€â”€â”€ Baris Grafik 1: Tren Omzet per Hari + Omzet per Jam â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
<div class="row g-4 mb-4">

    {{-- Tren Omzet per Hari (Line Chart) --}}
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-graph-up text-primary me-2"></i>
                Tren Omzet Harian
                <small class="text-muted ms-2">({{ $dateFrom->translatedFormat('d M Y') }} â€“ {{ $dateTo->translatedFormat('d M Y') }})</small>
            </div>
            <div class="card-body">
                @if($trendDays->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-bar-chart-line fs-1 d-block mb-2 opacity-25"></i>
                        Belum ada data transaksi pada periode ini.
                    </div>
                @else
                    <canvas id="chartTrendHari" height="100"></canvas>
                @endif
            </div>
        </div>
    </div>

    {{-- Omzet per Jam Hari Ini (Bar Chart) --}}
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-clock-history text-success me-2"></i>
                Omzet per Jam (Hari Ini)
            </div>
            <div class="card-body">
                @if($trendJam->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-clock fs-1 d-block mb-2 opacity-25"></i>
                        Belum ada transaksi hari ini.
                    </div>
                @else
                    <canvas id="chartJam" height="180"></canvas>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- â”€â”€â”€ Baris Grafik 2: Per Hari dalam Seminggu + Top Menu + Kategori â”€â”€â”€ --}}
<div class="row g-4 mb-4">

    {{-- Rata-rata Omzet per Hari dalam Seminggu (Bar) --}}
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-bar-chart-steps text-warning me-2"></i>
                Omzet per Hari dalam Seminggu
            </div>
            <div class="card-body">
                @if($trendHari->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-calendar-week fs-1 d-block mb-2 opacity-25"></i>
                        Belum ada data.
                    </div>
                @else
                    <canvas id="chartHariMinggu" height="140"></canvas>
                @endif
            </div>
        </div>
    </div>

    {{-- Top 10 Menu Terlaris (Horizontal Bar) --}}
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-trophy-fill text-danger me-2"></i>
                Top 10 Menu Terlaris (Omzet)
            </div>
            <div class="card-body">
                @if($topMenus->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-cup-hot fs-1 d-block mb-2 opacity-25"></i>
                        Belum ada data.
                    </div>
                @else
                    <canvas id="chartTopMenu" height="120"></canvas>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- â”€â”€â”€ Baris Grafik 3: Omzet per Kategori + Tabel Top Menu â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
<div class="row g-4 mb-4">

    {{-- Pie / Donut per Kategori --}}
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-pie-chart-fill text-info me-2"></i>
                Distribusi Omzet per Kategori
            </div>
            <div class="card-body d-flex justify-content-center align-items-center">
                @if($topKategori->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-tags fs-1 d-block mb-2 opacity-25"></i>
                        Belum ada data.
                    </div>
                @else
                    <canvas id="chartKategori" style="max-height:280px;"></canvas>
                @endif
            </div>
        </div>
    </div>

    {{-- Tabel Top 10 Menu Terlaris --}}
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="bi bi-list-ol text-primary me-2"></i>
                Rincian Top Menu Terlaris
            </div>
            <div class="card-body p-0">
                @if($topMenus->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-table fs-1 d-block mb-2 opacity-25"></i>
                        Belum ada data.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Nama Menu</th>
                                    <th class="text-center">Qty Terjual</th>
                                    <th class="text-end pe-3">Omzet</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topMenus as $i => $m)
                                    <tr>
                                        <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                                        <td class="fw-semibold">{{ $m->nama }}</td>
                                        <td class="text-center">{{ number_format($m->qty, 0, ',', '.') }}</td>
                                        <td class="text-end pe-3">@rupiah($m->omzet)</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- â”€â”€â”€ Quick-action buttons â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
<div class="card shadow-sm mb-4 p-3">
    <div class="d-flex flex-wrap gap-2 justify-content-center">
        @if(in_array(auth()->user()->role, ['admin', 'kasir']))
            <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-cart-plus me-1"></i> Buka Kasir
            </a>
        @endif
        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-receipt me-1"></i> Riwayat Transaksi
        </a>
        @if(in_array(auth()->user()->role, ['admin', 'owner']))
            <a href="{{ route('analysis.fp-growth') }}" class="btn btn-outline-info btn-sm">
                <i class="bi bi-diagram-3 me-1"></i> Analisis FP-Growth
            </a>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-success btn-sm">
                <i class="bi bi-file-earmark-bar-graph me-1"></i> Laporan
            </a>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // â”€â”€ Warna Palette â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    const COLORS = [
        '#e74a3b','#4e73df','#1cc88a','#f6c23e','#36b9cc',
        '#fd7e14','#6f42c1','#20c9a6','#858796','#5a5c69'
    ];

    const fmtRupiah = (n) =>
        'Rp ' + Number(n).toLocaleString('id-ID', { minimumFractionDigits: 0 });

    const fmtAxis = (v) =>
        'Rp ' + (v >= 1000000 ? (v / 1000000).toFixed(1) + 'jt' : v.toLocaleString('id-ID'));

    // â”€â”€ 1. Tren Omzet Harian (Line + Bar combo) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    @if($trendDays->isNotEmpty())
    (function () {
        const labels = @json($chartTrendLabels);
        const omzet  = @json($chartTrendOmzet);
        const jumlah = @json($chartTrendJumlah);

        new Chart(document.getElementById('chartTrendHari'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'Omzet (Rp)',
                        data: omzet,
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78,115,223,0.1)',
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'yOmzet',
                        pointRadius: omzet.length <= 14 ? 4 : 2,
                    },
                    {
                        type: 'bar',
                        label: 'Jumlah Transaksi',
                        data: jumlah,
                        backgroundColor: 'rgba(28,200,138,0.4)',
                        borderColor: '#1cc88a',
                        borderWidth: 1,
                        yAxisID: 'yJumlah',
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                if (ctx.dataset.yAxisID === 'yOmzet')
                                    return ' Omzet: ' + fmtRupiah(ctx.raw);
                                return ' Transaksi: ' + ctx.raw;
                            }
                        }
                    }
                },
                scales: {
                    yOmzet: {
                        type: 'linear',
                        position: 'left',
                        ticks: { callback: fmtAxis },
                        grid: { drawOnChartArea: true }
                    },
                    yJumlah: {
                        type: 'linear',
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    })();
    @endif

    // â”€â”€ 2. Omzet per Jam Hari Ini (Bar) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    @if($trendJam->isNotEmpty())
    (function () {
        const labels = @json($chartJamLabels);
        const omzet  = @json($chartJamOmzet);

        new Chart(document.getElementById('chartJam'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Omzet',
                    data: omzet,
                    backgroundColor: 'rgba(28,200,138,0.7)',
                    borderColor: '#1cc88a',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => ' ' + fmtRupiah(ctx.raw) } }
                },
                scales: { y: { ticks: { callback: fmtAxis } } }
            }
        });
    })();
    @endif

    // â”€â”€ 3. Omzet per Hari dalam Seminggu (Horizontal Bar) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    @if(count($chartHariLabels) > 0)
    (function () {
        const labels = @json($chartHariLabels);
        const omzet  = @json($chartHariOmzet);

        new Chart(document.getElementById('chartHariMinggu'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Omzet',
                    data: omzet,
                    backgroundColor: labels.map((_, i) => COLORS[i % COLORS.length]),
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => ' ' + fmtRupiah(ctx.raw) } }
                },
                scales: { x: { ticks: { callback: fmtAxis } } }
            }
        });
    })();
    @endif

    // â”€â”€ 4. Top 10 Menu Terlaris (Horizontal Bar) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    @if($topMenus->isNotEmpty())
    (function () {
        const labels = @json($chartMenuLabels);
        const omzet  = @json($chartMenuOmzet);

        new Chart(document.getElementById('chartTopMenu'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Omzet',
                    data: omzet,
                    backgroundColor: labels.map((_, i) => COLORS[i % COLORS.length]),
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => ' ' + fmtRupiah(ctx.raw) } }
                },
                scales: { x: { ticks: { callback: fmtAxis } } }
            }
        });
    })();
    @endif

    // â”€â”€ 5. Distribusi Omzet per Kategori (Donut) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    @if($topKategori->isNotEmpty())
    (function () {
        const labels = @json($chartKatLabels);
        const omzet  = @json($chartKatOmzet);

        new Chart(document.getElementById('chartKategori'), {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: omzet,
                    backgroundColor: COLORS.slice(0, labels.length),
                    borderWidth: 2,
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                cutout: '55%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12 } },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct   = ((ctx.raw / total) * 100).toFixed(1);
                                return ' ' + fmtRupiah(ctx.raw) + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    })();
    @endif

});
</script>
@endpush
