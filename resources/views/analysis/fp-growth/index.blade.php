@extends('layouts.app')

@section('title', 'Analisis FP-Growth')
@section('page-title', 'Analisis FP-Growth – Market Basket Analysis')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ─── Form Analisis Baru ──────────────────────────────────────────────── --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold py-3">
        <i class="bi bi-plus-circle-fill text-primary me-2"></i>Jalankan Analisis Baru
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('analysis.fp-growth.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Nama Analisis</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           placeholder="Mis: Analisis Oktober 2025"
                           value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control @error('date_from') is-invalid @enderror"
                           value="{{ old('date_from', now()->subDays(29)->toDateString()) }}" required>
                    @error('date_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control @error('date_to') is-invalid @enderror"
                           value="{{ old('date_to', now()->toDateString()) }}" required>
                    @error('date_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Min. Support (0–1)</label>
                    <input type="number" name="min_support" step="0.01" min="0.01" max="1"
                           class="form-control @error('min_support') is-invalid @enderror"
                           value="{{ old('min_support', 0.05) }}" required>
                    @error('min_support')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Min. Confidence (0–1)</label>
                    <input type="number" name="min_confidence" step="0.01" min="0.01" max="1"
                           class="form-control @error('min_confidence') is-invalid @enderror"
                           value="{{ old('min_confidence', 0.6) }}" required>
                    @error('min_confidence')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-play-fill me-1"></i>Jalankan Analisis
                    </button>
                    <small class="text-muted ms-3">
                        <i class="bi bi-info-circle"></i>
                        Proses berjalan sinkron. Tunggu hingga selesai sebelum menutup halaman.
                    </small>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ─── Riwayat Analisis ─────────────────────────────────────────────── --}}
<div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold py-3">
        <i class="bi bi-clock-history text-secondary me-2"></i>Riwayat Analisis
    </div>
    <div class="card-body p-0">
        @if($analyses->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-diagram-3 fs-1 d-block mb-2 opacity-25"></i>
                Belum ada analisis yang dijalankan.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Nama</th>
                            <th>Periode</th>
                            <th>Min. Support</th>
                            <th>Min. Confidence</th>
                            <th>Status</th>
                            <th>Dibuat Oleh</th>
                            <th>Waktu</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($analyses as $a)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $a->name }}</td>
                            <td class="text-nowrap">
                                {{ $a->date_from->format('d/m/Y') }} –
                                {{ $a->date_to->format('d/m/Y') }}
                            </td>
                            <td>{{ number_format($a->min_support * 100, 1) }}%</td>
                            <td>{{ number_format($a->min_confidence * 100, 1) }}%</td>
                            <td>
                                @php
                                    $badge = match($a->status) {
                                        'done'    => 'success',
                                        'running' => 'primary',
                                        'failed'  => 'danger',
                                        default   => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ ucfirst($a->status) }}</span>
                            </td>
                            <td>{{ $a->creator?->name ?? '-' }}</td>
                            <td class="text-nowrap small text-muted">
                                {{ $a->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-end pe-3">
                                @if($a->status === 'done')
                                    <a href="{{ route('analysis.fp-growth.show', $a) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('analysis.fp-growth.destroy', $a) }}"
                                      class="d-inline" id="del-fpg-{{ $a->id }}">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-confirm
                                            data-confirm-title="Hapus Analisis?"
                                            data-confirm-message="Analisis &quot;{{ $a->name }}&quot; dan semua hasilnya akan dihapus permanen."
                                            data-confirm-btn="Ya, Hapus"
                                            data-confirm-form="del-fpg-{{ $a->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2">
                {{ $analyses->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
