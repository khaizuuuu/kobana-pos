@extends('layouts.app')

@section('title', 'Detail Transaksi ' . $transaction->transaction_code)
@section('page-title', 'Detail Transaksi: ' . $transaction->transaction_code)

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-receipt-cutoff text-danger"></i> Rincian Nota Transaksi
                </h6>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Cetak Struk
                    </button>
                    @if(auth()->user()?->role === 'admin')
                        <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                    <div class="col-sm-6 col-md-3">
                        <div class="text-muted small text-uppercase fw-semibold">Kode Transaksi</div>
                        <div class="fw-bold fs-6 text-dark">{{ $transaction->transaction_code }}</div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-muted small text-uppercase fw-semibold">Tanggal & Waktu</div>
                        <div class="fw-semibold text-dark">{{ $transaction->hari_indonesia }}, {{ $transaction->formatted_date }} <span class="text-muted small">{{ $transaction->transaction_time }}</span></div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-muted small text-uppercase fw-semibold">Kasir / Pembuat</div>
                        <div class="fw-semibold text-dark">{{ $transaction->creator?->name ?? 'Kasir Kobana' }}</div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-muted small text-uppercase fw-semibold">Total Pembayaran</div>
                        <div class="fw-bold fs-6 text-success">{{ $transaction->formatted_amount }}</div>
                    </div>
                </div>

                <h6 class="fw-bold mb-3">Item Pembelian</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Menu</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Diskon</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaction->items as $index => $item)
                                <tr>
                                    <td class="text-muted small">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $item->menu?->name ?? 'Menu #' . $item->menu_id }}</div>
                                        <div class="small text-muted">Kode: {{ $item->menu?->code ?? '-' }}</div>
                                    </td>
                                    <td class="text-end">{{ $item->formatted_price }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary bg-opacity-10 text-dark px-2 py-1">
                                            {{ $item->qty }}
                                        </span>
                                    </td>
                                    <td class="text-end text-danger">
                                        {{ $item->discount > 0 ? '-' . $item->formatted_discount : '-' }}
                                    </td>
                                    <td class="text-end fw-bold text-dark">
                                        {{ $item->formatted_subtotal }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="3" class="text-end">Total Item & Bayar:</td>
                                <td class="text-center">{{ $transaction->total_items }}</td>
                                <td></td>
                                <td class="text-end text-success fs-6">{{ $transaction->formatted_amount }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white py-3 d-flex justify-content-between">
                <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
                </a>
                @if(auth()->user()?->role === 'admin')
                    <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="d-inline" id="del-trx-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                data-confirm
                                data-confirm-title="Hapus Transaksi?"
                                data-confirm-message="Yakin ingin menghapus transaksi {{ $transaction->transaction_code }}? Data tidak dapat dikembalikan."
                                data-confirm-btn="Ya, Hapus"
                                data-confirm-form="del-trx-form">
                            <i class="bi bi-trash me-1"></i> Hapus Transaksi
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
