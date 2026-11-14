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
                <!-- Info Header Transaksi & Cabang -->
                <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                    <div class="col-sm-6 col-md-3">
                        <div class="text-muted small text-uppercase fw-semibold">Kode Transaksi</div>
                        <div class="fw-bold fs-6 text-dark">{{ $transaction->transaction_code }}</div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-muted small text-uppercase fw-semibold">Cabang Toko</div>
                        <div class="fw-bold fs-6 text-danger">
                            <i class="bi bi-shop me-1"></i>{{ $transaction->branch?->name ?? 'Kobana Pusat' }}
                        </div>
                        <small class="text-muted d-block">{{ $transaction->branch?->address }}</small>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-muted small text-uppercase fw-semibold">Tanggal & Waktu</div>
                        <div class="fw-semibold text-dark">{{ $transaction->hari_indonesia }}, {{ $transaction->formatted_date }}</div>
                        <small class="text-muted">{{ $transaction->transaction_time }}</small>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-muted small text-uppercase fw-semibold">Kasir Bertugas</div>
                        <div class="fw-semibold text-dark">{{ $transaction->creator?->name ?? 'Kasir Kobana' }}</div>
                        <small class="text-muted">{{ $transaction->creator?->role ? ucfirst($transaction->creator->role) : 'Kasir' }}</small>
                    </div>
                </div>

                <!-- Info Pembayaran -->
                <div class="p-3 mb-4 rounded-3 border bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <div class="small text-muted fw-semibold">METODE PEMBAYARAN</div>
                        <div class="mt-1">
                            @if($transaction->payment_method === 'qris')
                                <span class="badge bg-primary px-3 py-2 fs-6">
                                    <i class="bi bi-qr-code-scan me-1"></i> QRIS
                                </span>
                            @elseif($transaction->payment_method === 'transfer')
                                <span class="badge bg-info text-dark px-3 py-2 fs-6">
                                    <i class="bi bi-bank me-1"></i> Transfer Bank
                                </span>
                            @else
                                <span class="badge bg-success px-3 py-2 fs-6">
                                    <i class="bi bi-cash-stack me-1"></i> Cash (Tunai)
                                </span>
                            @endif
                            @if($transaction->payment_note)
                                <span class="text-muted small ms-2"><i class="bi bi-info-circle me-1"></i>{{ $transaction->payment_note }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-md-end">
                        <div class="small text-muted fw-semibold">STATUS TRANSAKSI</div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 mt-1">
                            <i class="bi bi-check-circle-fill me-1"></i> Lunas / Berhasil
                        </span>
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
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Total Item & Bayar:</td>
                                <td class="text-center fw-bold">{{ $transaction->total_items }}</td>
                                <td></td>
                                <td class="text-end text-success fs-5 fw-bold">{{ $transaction->formatted_amount }}</td>
                            </tr>
                            @if($transaction->payment_method === 'cash' && $transaction->cash_amount > 0)
                                <tr>
                                    <td colspan="5" class="text-end text-muted small">Uang Diterima (Cash):</td>
                                    <td class="text-end fw-semibold">@rupiah($transaction->cash_amount)</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end text-muted small">Kembalian:</td>
                                    <td class="text-end fw-bold text-primary">@rupiah($transaction->change_amount)</td>
                                </tr>
                            @endif
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

    <!-- Panel Samping: Bukti QRIS -->
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-camera-fill text-primary"></i> Bukti Pembayaran QRIS
                </h6>
            </div>
            <div class="card-body text-center">
                @if($transaction->payment_proof)
                    <div class="position-relative mb-3">
                        <img src="{{ $transaction->payment_proof_url }}" alt="Bukti QRIS {{ $transaction->transaction_code }}" class="img-fluid rounded border shadow-sm" style="max-height: 280px; object-fit: contain; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#proofModal">
                    </div>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#proofModal">
                            <i class="bi bi-arrows-fullscreen me-1"></i> Perbesar Foto
                        </button>
                        <a href="{{ $transaction->payment_proof_url }}" target="_blank" download class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-download me-1"></i> Unduh
                        </a>
                    </div>
                @else
                    <div class="py-4 text-muted">
                        <i class="bi bi-image text-secondary fs-1 d-block mb-2 opacity-50"></i>
                        <p class="small mb-0">Tidak ada foto bukti transaksi QRIS yang dilampirkan.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($transaction->payment_proof)
<!-- Modal Bukti Transaksi -->
<div class="modal fade" id="proofModal" tabindex="-1" aria-labelledby="proofModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fs-6 fw-bold" id="proofModalLabel">
                    <i class="bi bi-camera me-1 text-primary"></i> Bukti Transaksi QRIS - {{ $transaction->transaction_code }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-2 bg-dark rounded-bottom">
                <img src="{{ $transaction->payment_proof_url }}" alt="Bukti QRIS" class="img-fluid rounded" style="max-height: 75vh; object-fit: contain;">
            </div>
            <div class="modal-footer py-2">
                <a href="{{ $transaction->payment_proof_url }}" target="_blank" class="btn btn-sm btn-primary">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Tab Baru
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
