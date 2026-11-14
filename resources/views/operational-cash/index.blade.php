@extends('layouts.app')

@section('title', 'Kas & Belanja Harian')
@section('page-title', 'Buku Kas & Belanja Harian Warung')

@section('content')
{{-- ─── Kartu Ringkasan ──────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm h-100 p-3 border-start border-danger border-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-danger bg-opacity-10 p-2 text-danger">
                    <i class="bi bi-cart-dash-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Belanja / Pengeluaran</div>
                    <div class="fw-bold fs-5 text-danger">@rupiah($totalPengeluaran)</div>
                    <small class="text-muted">Beras, gas, minyak, sabun, dll</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100 p-3 border-start border-success border-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success bg-opacity-10 p-2 text-success">
                    <i class="bi bi-piggy-bank-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Tabungan / Setoran</div>
                    <div class="fw-bold fs-5 text-success">@rupiah($totalTabungan)</div>
                    <small class="text-muted">Disisihkan dari omzet harian</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100 p-3 border-start border-primary border-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 p-2 text-primary">
                    <i class="bi bi-journal-text fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Catatan Kas</div>
                    <div class="fw-bold fs-5 text-dark">{{ number_format($countTotal, 0, ',', '.') }} Transaksi</div>
                    <small class="text-muted">Pada periode filter terpilih</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
            <i class="bi bi-wallet2 text-danger"></i> Daftar Pengeluaran Belanja & Tabungan Kasir
        </h6>
        @if(in_array(auth()->user()->role, ['admin', 'kasir']))
        <a href="{{ route('operational-cash.create') }}" class="btn btn-danger btn-sm shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> + Catat Belanja / Tabungan
        </a>
        @endif
    </div>

    {{-- ─── Filter Bar ─────────────────────────────────────────────────────── --}}
    <div class="card-body border-bottom bg-light py-3">
        <form method="GET" action="{{ route('operational-cash.index') }}" class="row g-2 align-items-end">
            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-semibold mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom->toDateString() }}">
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-semibold mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo->toDateString() }}">
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-semibold mb-1">Jenis Kas</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Semua Jenis</option>
                    <option value="pengeluaran" {{ $type === 'pengeluaran' ? 'selected' : '' }}>Belanja Barang (Keluar)</option>
                    <option value="tabungan" {{ $type === 'tabungan' ? 'selected' : '' }}>Tabungan / Setoran (Masuk)</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-semibold mb-1">Cabang</label>
                @if(auth()->user()->isKasir() && auth()->user()->branch_id)
                    <div class="form-control form-control-sm bg-light text-muted text-truncate">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ auth()->user()->branch?->name }}
                    </div>
                @else
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-semibold mb-1">Cari Keperluan</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Beras, gas, kantong..." value="{{ $search }}">
            </div>
            <div class="col-md-2 col-sm-6 d-flex gap-1">
                <button type="submit" class="btn btn-secondary btn-sm flex-fill">
                    <i class="bi bi-filter"></i> Filter
                </button>
                @if(request()->hasAny(['date_from', 'date_to', 'type', 'branch_id', 'search']))
                    <a href="{{ route('operational-cash.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ─── Tabel Riwayat Kas ───────────────────────────────────────────────── --}}
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Waktu</th>
                        <th>Cabang</th>
                        <th>Kasir</th>
                        <th>Jenis</th>
                        <th>Nama Barang / Keperluan</th>
                        <th>Kategori</th>
                        <th class="text-end">Nominal (Rp)</th>
                        <th>Catatan</th>
                        @if(in_array(auth()->user()->role, ['admin', 'kasir']))
                        <th style="width: 100px;" class="text-end">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $index => $row)
                        <tr>
                            <td class="text-muted small">{{ $records->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $row->date->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $row->time ? substr($row->time, 0, 5) : '-' }}</small>
                            </td>
                            <td>
                                @if($row->branch)
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $row->branch->name }}
                                    </span>
                                @else
                                    <span class="text-muted small">Pusat</span>
                                @endif
                            </td>
                            <td>
                                <span class="small fw-semibold text-dark">{{ $row->user?->name ?? 'Kasir' }}</span>
                            </td>
                            <td>
                                @if($row->type === 'tabungan')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                        <i class="bi bi-piggy-bank me-1"></i> Tabungan
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">
                                        <i class="bi bi-cart-dash me-1"></i> Belanja Barang
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $row->name }}</div>
                            </td>
                            <td>
                                @if($row->category)
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 small">
                                        {{ $row->category }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($row->type === 'tabungan')
                                    <span class="fw-bold text-success fs-6">+ @rupiah($row->amount)</span>
                                @else
                                    <span class="fw-bold text-danger fs-6">- @rupiah($row->amount)</span>
                                @endif
                            </td>
                            <td class="small text-muted" style="max-width: 200px;">
                                {{ $row->notes ?? '-' }}
                            </td>
                            @if(in_array(auth()->user()->role, ['admin', 'kasir']))
                            <td class="text-end">
                                @if(auth()->user()->isAdmin() || ($row->user_id === auth()->id() && $row->date->isToday()))
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('operational-cash.edit', $row) }}" class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <form action="{{ route('operational-cash.destroy', $row) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus" data-confirm-delete data-confirm-title="Hapus Catatan Kas" data-confirm-message="Yakin ingin menghapus catatan &quot;{{ $row->name }}&quot;?">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ in_array(auth()->user()->role, ['admin', 'kasir']) ? 10 : 9 }}" class="text-center py-5 text-muted">
                                <i class="bi bi-wallet2 fs-1 d-block mb-2 opacity-50"></i>
                                Belum ada catatan pengeluaran belanja atau tabungan pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($records->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $records->links() }}
        </div>
    @endif
</div>
@endsection
