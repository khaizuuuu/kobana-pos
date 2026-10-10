@extends('layouts.app')

@section('title', 'Laporan Penjualan')
@section('page-title', 'Laporan Penjualan')

@section('content')

{{-- ─── Filter ─────────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('reports.index') }}" class="card shadow-sm mb-4 p-3">
    <div class="row g-2 align-items-end">
        <div class="col-sm-3">
            <label class="form-label fw-semibold small mb-1">Dari Tanggal</label>
            <input type="date" name="date_from" class="form-control form-control-sm"
                   value="{{ $dateFrom->toDateString() }}">
        </div>
        <div class="col-sm-3">
            <label class="form-label fw-semibold small mb-1">Sampai Tanggal</label>
            <input type="date" name="date_to" class="form-control form-control-sm"
                   value="{{ $dateTo->toDateString() }}">
        </div>
        <div class="col-sm-3">
            <label class="form-label fw-semibold small mb-1">Kategori</label>
            <select name="category_id" class="form-select form-select-sm">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </div>
</form>

{{-- ─── Tombol Ekspor ────────────────────────────────────────────────── --}}
<div class="d-flex gap-2 mb-4 flex-wrap">
    <a href="{{ route('reports.export-excel', request()->query()) }}"
       class="btn btn-success btn-sm">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Ekspor CSV / Excel
    </a>
    <a href="{{ route('reports.export-pdf', request()->query()) }}"
       target="_blank"
       class="btn btn-danger btn-sm">
        <i class="bi bi-file-earmark-pdf me-1"></i> Ekspor PDF (Print)
    </a>
</div>

{{-- ─── Kartu Ringkasan ─────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center p-3">
            <div class="text-muted small text-uppercase fw-semibold">Total Omzet</div>
            <div class="fw-bold fs-6">@rupiah($totalOmzet)</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center p-3">
            <div class="text-muted small text-uppercase fw-semibold">Total Transaksi</div>
            <div class="fw-bold fs-6">{{ number_format($totalTrx, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center p-3">
            <div class="text-muted small text-uppercase fw-semibold">Item Terjual</div>
            <div class="fw-bold fs-6">{{ number_format($totalItem, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center p-3">
            <div class="text-muted small text-uppercase fw-semibold">Rata-rata Trx</div>
            <div class="fw-bold fs-6">@rupiah($avgTrx)</div>
        </div>
    </div>
</div>

{{-- ─── Laporan Harian ──────────────────────────────────────────────── --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold py-3">
        <i class="bi bi-calendar3 text-primary me-2"></i>Laporan Harian
    </div>
    <div class="card-body p-0">
        @if($daily->isEmpty())
            <div class="text-center text-muted py-4">Tidak ada data pada periode ini.</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tanggal</th>
                            <th>Hari</th>
                            <th class="text-center">Transaksi</th>
                            <th class="text-center">Item Terjual</th>
                            <th class="text-end pe-3">Omzet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($daily as $d)
                        <tr>
                            <td class="ps-3">{{ \Carbon\Carbon::parse($d->tgl)->format('d/m/Y') }}</td>
                            <td>{{ $d->hari }}</td>
                            <td class="text-center">{{ number_format($d->jumlah, 0, ',', '.') }}</td>
                            <td class="text-center">{{ number_format($d->item, 0, ',', '.') }}</td>
                            <td class="text-end pe-3 fw-semibold">@rupiah($d->omzet)</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td class="ps-3" colspan="2">Total</td>
                            <td class="text-center">{{ number_format($daily->sum('jumlah'), 0, ',', '.') }}</td>
                            <td class="text-center">{{ number_format($daily->sum('item'), 0, ',', '.') }}</td>
                            <td class="text-end pe-3">@rupiah($daily->sum('omzet'))</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- ─── Laporan per Menu ─────────────────────────────────────────────── --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold py-3">
        <i class="bi bi-cup-hot-fill text-warning me-2"></i>Laporan per Menu
    </div>
    <div class="card-body p-0">
        @if($menuReport->isEmpty())
            <div class="text-center text-muted py-4">Tidak ada data menu pada periode ini.</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Kategori</th>
                            <th>Kode</th>
                            <th>Nama Menu</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end pe-3">Omzet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $lastKat = null; @endphp
                        @foreach($menuReport as $m)
                            @if($m->kategori !== $lastKat)
                                @php $lastKat = $m->kategori; @endphp
                                <tr class="table-secondary">
                                    <td class="ps-3 fw-semibold" colspan="5">{{ $m->kategori }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="ps-3 text-muted small">—</td>
                                <td><code>{{ $m->kode }}</code></td>
                                <td>{{ $m->nama }}</td>
                                <td class="text-center">{{ number_format($m->qty, 0, ',', '.') }}</td>
                                <td class="text-end pe-3">@rupiah($m->omzet)</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td class="ps-3" colspan="3">Total</td>
                            <td class="text-center">{{ number_format($menuReport->sum('qty'), 0, ',', '.') }}</td>
                            <td class="text-end pe-3">@rupiah($menuReport->sum('omzet'))</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection
