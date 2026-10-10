@extends('layouts.app')

@section('title', 'Hasil FP-Growth – ' . $analysis->name)
@section('page-title', 'Hasil Analisis: ' . $analysis->name)

@section('content')

{{-- ─── Info Analisis ────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm p-3 text-center">
            <div class="text-muted small fw-semibold text-uppercase">Periode</div>
            <div class="fw-bold">{{ $analysis->date_from->format('d/m/Y') }} – {{ $analysis->date_to->format('d/m/Y') }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm p-3 text-center">
            <div class="text-muted small fw-semibold text-uppercase">Min. Support</div>
            <div class="fw-bold">{{ number_format($analysis->min_support * 100, 1) }}%</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm p-3 text-center">
            <div class="text-muted small fw-semibold text-uppercase">Min. Confidence</div>
            <div class="fw-bold">{{ number_format($analysis->min_confidence * 100, 1) }}%</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm p-3 text-center">
            <div class="text-muted small fw-semibold text-uppercase">Total Rules</div>
            <div class="fw-bold">{{ $analysis->rules()->count() }}</div>
        </div>
    </div>
</div>

{{-- ─── Grafik: Top 10 Rules berdasarkan Lift ──────────────────────── --}}
@if($rules->isNotEmpty())
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold py-3">
        <i class="bi bi-bar-chart-line-fill text-info me-2"></i>Top Rules (Lift Tertinggi)
    </div>
    <div class="card-body">
        <canvas id="chartRules" height="80"></canvas>
    </div>
</div>
@endif

{{-- ─── Tabel Frequent Itemsets ─────────────────────────────────────── --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold py-3">
        <i class="bi bi-collection-fill text-warning me-2"></i>
        Frequent Itemsets ({{ $analysis->itemsets()->count() }} ditemukan)
    </div>
    <div class="card-body p-0">
        @if($itemsets->isEmpty())
            <div class="text-center text-muted py-4">Tidak ada frequent itemset yang memenuhi min. support.</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Itemset (Nama Menu)</th>
                            <th class="text-center">Ukuran</th>
                            <th class="text-center">Support Count</th>
                            <th class="text-center">Support (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($itemsets as $idx => $set)
                        <tr>
                            <td class="ps-3 text-muted">{{ ($itemsets->currentPage() - 1) * $itemsets->perPage() + $idx + 1 }}</td>
                            <td>
                                @foreach((array) $set->items as $menuId)
                                    <span class="badge bg-secondary me-1">{{ $menuMap[$menuId] ?? "Menu #$menuId" }}</span>
                                @endforeach
                            </td>
                            <td class="text-center">{{ $set->itemset_size }}</td>
                            <td class="text-center">{{ $set->support_count }}</td>
                            <td class="text-center">{{ number_format($set->support * 100, 2) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2">{{ $itemsets->appends(['rPage' => request('rPage')])->links() }}</div>
        @endif
    </div>
</div>

{{-- ─── Tabel Association Rules ─────────────────────────────────────── --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold py-3">
        <i class="bi bi-arrow-left-right text-success me-2"></i>
        Association Rules ({{ $analysis->rules()->count() }} ditemukan)
    </div>
    <div class="card-body p-0">
        @if($rules->isEmpty())
            <div class="text-center text-muted py-4">Tidak ada rules yang memenuhi min. confidence.</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Jika Membeli (Antecedent)</th>
                            <th>Maka Juga Membeli (Consequent)</th>
                            <th class="text-center">Support</th>
                            <th class="text-center">Confidence</th>
                            <th class="text-center">Lift</th>
                            <th class="text-center">Insight</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rules as $idx => $rule)
                        <tr>
                            <td class="ps-3 text-muted">{{ ($rules->currentPage() - 1) * $rules->perPage() + $idx + 1 }}</td>
                            <td>
                                @foreach((array) $rule->antecedent as $menuId)
                                    <span class="badge bg-danger me-1">{{ $menuMap[$menuId] ?? "Menu #$menuId" }}</span>
                                @endforeach
                            </td>
                            <td>
                                @foreach((array) $rule->consequent as $menuId)
                                    <span class="badge bg-success me-1">{{ $menuMap[$menuId] ?? "Menu #$menuId" }}</span>
                                @endforeach
                            </td>
                            <td class="text-center">{{ number_format($rule->support * 100, 2) }}%</td>
                            <td class="text-center">{{ number_format($rule->confidence * 100, 2) }}%</td>
                            <td class="text-center">
                                <span class="fw-bold {{ $rule->lift > 1 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($rule->lift, 3) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($rule->lift > 1.5)
                                    <span class="badge bg-success">Kuat</span>
                                @elseif($rule->lift > 1)
                                    <span class="badge bg-primary">Positif</span>
                                @else
                                    <span class="badge bg-warning text-dark">Lemah</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2">{{ $rules->appends(['iPage' => request('iPage')])->links() }}</div>
        @endif
    </div>
</div>

<a href="{{ route('analysis.fp-growth') }}" class="btn btn-outline-secondary mb-4">
    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Analisis
</a>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if($rules->isNotEmpty())
    (function () {
        // Top 10 rules by lift for chart
        const rules = @json($chartRules ?? []);

        new Chart(document.getElementById('chartRules'), {
            type: 'bar',
            data: {
                labels: rules.map(r => r.label),
                datasets: [
                    {
                        label: 'Lift',
                        data: rules.map(r => r.lift),
                        backgroundColor: 'rgba(78,115,223,0.7)',
                        borderRadius: 4,
                        yAxisID: 'yLift',
                    },
                    {
                        type: 'line',
                        label: 'Confidence (%)',
                        data: rules.map(r => r.confidence),
                        borderColor: '#1cc88a',
                        backgroundColor: 'rgba(28,200,138,0.15)',
                        fill: false,
                        yAxisID: 'yConf',
                        pointRadius: 5,
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    yLift: { type: 'linear', position: 'left', title: { display: true, text: 'Lift' } },
                    yConf: { type: 'linear', position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Confidence (%)' } },
                    x: { ticks: { maxRotation: 30, font: { size: 10 } } }
                }
            }
        });
    })();
    @endif
});
</script>
@endpush
