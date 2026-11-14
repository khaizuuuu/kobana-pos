@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('page-title', 'Riwayat Transaksi Penjualan')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
            <i class="bi bi-receipt text-danger"></i> Daftar Transaksi Penjualan
        </h6>
        @if(in_array(auth()->user()?->role, ['admin', 'kasir']))
            <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-cart-plus-fill me-1"></i> Buka Kasir / Input Baru
            </a>
        @endif
    </div>

    <!-- Filter Bar -->
    <div class="card-body border-bottom bg-light py-3">
        <form method="GET" action="{{ route('transactions.index') }}" class="row g-2 align-items-center">
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" placeholder="Dari Tanggal">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" placeholder="Sampai Tanggal">
            </div>
            <div class="col-md-2">
                <select name="branch_id" class="form-select form-select-sm">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="payment_method" class="form-select form-select-sm">
                    <option value="">Semua Pembayaran</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash (Tunai)</option>
                    <option value="qris" {{ request('payment_method') === 'qris' ? 'selected' : '' }}>QRIS</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" name="code" class="form-control form-control-sm" value="{{ request('code') }}" placeholder="Kode TRX...">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary btn-sm flex-fill">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                @if(request()->hasAny(['date_from', 'date_to', 'branch_id', 'payment_method', 'code', 'menu']))
                    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 135px;">Kode Transaksi</th>
                        <th>Waktu & Tanggal</th>
                        <th>Cabang</th>
                        <th>Metode Bayar</th>
                        <th class="text-center" style="width: 80px;">Item</th>
                        <th class="text-end" style="width: 130px;">Total Bayar</th>
                        <th>Kasir</th>
                        <th style="width: 110px;" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trx)
                        <tr>
                            <td>
                                <a href="{{ route('transactions.show', $trx) }}" class="fw-bold text-primary text-decoration-none">
                                    {{ $trx->transaction_code }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $trx->hari_indonesia }}, {{ $trx->formatted_date }}</div>
                                <small class="text-muted">{{ $trx->transaction_time }}</small>
                            </td>
                            <td>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">
                                    <i class="bi bi-shop me-1"></i>{{ $trx->branch?->name ?? 'Pusat' }}
                                </span>
                            </td>
                            <td>
                                @if($trx->payment_method === 'qris')
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                                        <i class="bi bi-qr-code-scan me-1"></i> QRIS
                                    </span>
                                    @if($trx->payment_proof)
                                        <a href="{{ $trx->payment_proof_url }}" target="_blank" class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 text-decoration-none ms-1" title="Lihat Foto Bukti QRIS">
                                            <i class="bi bi-camera-fill"></i>
                                        </a>
                                    @endif
                                @elseif($trx->payment_method === 'transfer')
                                    <span class="badge bg-info bg-opacity-10 text-dark border border-info border-opacity-25 px-2 py-1">
                                        <i class="bi bi-bank me-1"></i> Transfer
                                    </span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                        <i class="bi bi-cash-stack me-1"></i> Cash
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary bg-opacity-10 text-dark border px-2 py-1">
                                    {{ $trx->total_items }}
                                </span>
                            </td>
                            <td class="text-end fw-bold text-success">
                                {{ $trx->formatted_amount }}
                            </td>
                            <td>
                                <span class="text-muted small">
                                    <i class="bi bi-person me-1"></i>{{ $trx->creator?->name ?? 'Kasir Kobana' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('transactions.show', $trx) }}" class="btn btn-outline-info" title="Lihat Rincian Nota">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    @if(auth()->user()?->role === 'admin')
                                        <a href="{{ route('transactions.edit', $trx) }}" class="btn btn-outline-primary" title="Edit Transaksi">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <form action="{{ route('transactions.destroy', $trx) }}" method="POST" class="d-inline" id="del-trx-{{ $trx->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-outline-danger btn-delete" title="Hapus Transaksi"
                                                    data-confirm
                                                    data-confirm-title="Hapus Transaksi?"
                                                    data-confirm-message="Yakin ingin menghapus transaksi {{ $trx->transaction_code }}? Data tidak dapat dikembalikan."
                                                    data-confirm-btn="Ya, Hapus"
                                                    data-confirm-form="del-trx-{{ $trx->id }}">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt fs-1 d-block mb-2 opacity-50"></i>
                                Belum ada riwayat transaksi penjualan yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($transactions->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $transactions->links() }}
        </div>
    @endif
</div>
@endsection
