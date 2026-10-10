@extends('layouts.app')

@section('title', 'Impor Dataset')
@section('page-title', 'Impor Dataset Penjualan')

@section('content')
<div class="row g-4 mb-4">
    <!-- Current DB Stats -->
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card bg-white shadow-sm h-100 p-3">
            <div class="d-flex align-items-center">
                <div class="rounded-3 bg-danger bg-opacity-10 p-3 text-danger me-3">
                    <i class="bi bi-cup-hot-fill fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small fw-semibold text-uppercase">Total Master Menu</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['menus_count'] }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card bg-white shadow-sm h-100 p-3">
            <div class="d-flex align-items-center">
                <div class="rounded-3 bg-warning bg-opacity-10 p-3 text-warning me-3">
                    <i class="bi bi-tags-fill fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small fw-semibold text-uppercase">Kategori</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['categories_count'] }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card bg-white shadow-sm h-100 p-3">
            <div class="d-flex align-items-center">
                <div class="rounded-3 bg-primary bg-opacity-10 p-3 text-primary me-3">
                    <i class="bi bi-receipt fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small fw-semibold text-uppercase">Total Transaksi</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['transactions_count'], 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card bg-white shadow-sm h-100 p-3">
            <div class="d-flex align-items-center">
                <div class="rounded-3 bg-success bg-opacity-10 p-3 text-success me-3">
                    <i class="bi bi-cash-stack fs-3"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 small fw-semibold text-uppercase">Total Omzet</h6>
                    <h3 class="fw-bold mb-0 text-dark">@rupiah($stats['total_amount'])</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Upload Card -->
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-cloud-arrow-up text-primary"></i> Upload File Dataset Excel
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info border-0 rounded-3 mb-4 small" role="alert">
                    <div class="d-flex gap-2">
                        <i class="bi bi-info-circle-fill fs-5 text-info"></i>
                        <div>
                            <strong>Struktur Dataset yang didukung:</strong>
                            <ul class="mb-0 ps-3 mt-1">
                                <li>Sheet <code>Master_Menu</code>: Kode, Menu, Kategori, Harga</li>
                                <li>Sheet <code>Header_Transaksi</code>: ID, Tanggal, Hari, Jam, Total_Item, Total_Bayar</li>
                                <li>Sheet <code>Detail_Transaksi</code>: ID, Kode, Menu, Qty, Harga, Subtotal</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <form action="{{ route('imports.store') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <div class="mb-4">
                        <label for="dataset_file" class="form-label fw-semibold">Pilih File (.xlsx / .xls)</label>
                        <input type="file" name="dataset_file" id="dataset_file" class="form-control @error('dataset_file') is-invalid @enderror" accept=".xlsx,.xls" required>
                        @error('dataset_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Maksimal ukuran file: 50MB. Proses impor bersifat <strong>idempoten</strong> (tidak menggandakan data jika diunggah ulang).</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" id="btnSubmitImport">
                        <i class="bi bi-upload me-1"></i> Mulai Impor Dataset
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- History Logs -->
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-secondary"></i> Riwayat Impor
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>File</th>
                                <th>Status</th>
                                <th>Menu / Trx / Item</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td class="small text-muted">{{ $log->created_at ? $log->created_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $log->file_name }}</div>
                                        @if(!empty($log->warnings))
                                            <span class="badge bg-warning text-dark small" title="{{ implode(', ', $log->warnings) }}">
                                                <i class="bi bi-exclamation-triangle-fill"></i> {{ count($log->warnings) }} Catatan
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->status === 'done')
                                            <span class="badge bg-success">Berhasil</span>
                                        @elseif($log->status === 'running')
                                            <span class="badge bg-primary">Memproses</span>
                                        @elseif($log->status === 'failed')
                                            <span class="badge bg-danger">Gagal</span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td class="small">
                                        <span class="badge bg-light text-dark border">{{ $log->rows_menu }} menu</span>
                                        <span class="badge bg-light text-dark border">{{ number_format($log->rows_transactions, 0, ',', '.') }} trx</span>
                                        <span class="badge bg-light text-dark border">{{ number_format($log->rows_items, 0, ',', '.') }} item</span>
                                    </td>
                                    <td class="small">{{ $log->creator?->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-3 d-block mb-1"></i> Belum ada riwayat impor data.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($logs->hasPages())
                <div class="card-footer bg-white py-2">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('importForm')?.addEventListener('submit', function() {
    window.showSwalLoading(
        'Sedang Mengimpor Dataset...',
        'Memproses Master Menu, Transaksi, dan Item Transaksi. Mohon jangan menutup halaman.'
    );
});
</script>
@endpush
