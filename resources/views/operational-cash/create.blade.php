@extends('layouts.app')

@section('title', 'Catat Kas & Belanja Harian')
@section('page-title', 'Catat Belanja Warung & Tabungan Kasir')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-wallet2 text-danger"></i> Formulir Kas Harian Kasir
                </h6>
                <a href="{{ route('operational-cash.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card-body p-4">
                {{-- Quick Chips untuk mempercepat input kasir --}}
                <div class="mb-4 p-3 bg-light rounded-3 border">
                    <div class="small fw-bold text-dark mb-2">
                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Pilihan Cepat Keperluan Harian Warung:
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-dark bg-white quick-chip" data-type="pengeluaran" data-category="Bahan Baku" data-name="Beras (Karung/Kg)">
                            🍚 Beras
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-dark bg-white quick-chip" data-type="pengeluaran" data-category="Gas & Energi" data-name="Gas LPG 3kg">
                            🔥 Gas LPG 3kg
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-dark bg-white quick-chip" data-type="pengeluaran" data-category="Kemasan & Plastik" data-name="Kantong Kresek (Plastik)">
                            🛍️ Kantong Kresek
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-dark bg-white quick-chip" data-type="pengeluaran" data-category="Bahan Baku" data-name="Minyak Goreng">
                            🛢️ Minyak Goreng
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-dark bg-white quick-chip" data-type="pengeluaran" data-category="Bahan Baku" data-name="Gula & Teh">
                            🍵 Gula & Teh
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-dark bg-white quick-chip" data-type="pengeluaran" data-category="Kebersihan & Sanitasi" data-name="Sabun Cuci Piring / Pembersih">
                            🧼 Sabun Cuci
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success bg-white quick-chip" data-type="tabungan" data-category="Tabungan Harian" data-name="Tabungan Hasil Penjualan Harian">
                            💰 Tabungan Harian
                        </button>
                    </div>
                    <div class="form-text small mt-1">Klik salah satu tombol di atas untuk mengisi otomatis nama dan jenis kas secara cepat.</div>
                </div>

                <form action="{{ route('operational-cash.store') }}" method="POST">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Catatan Kas <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type-pengeluaran" value="pengeluaran" {{ old('type', 'pengeluaran') === 'pengeluaran' ? 'checked' : '' }} required>
                                    <label class="btn btn-outline-danger w-100 py-2 text-center" for="type-pengeluaran">
                                        <i class="bi bi-cart-dash d-block fs-5 mb-1"></i>
                                        <span class="small fw-bold">Belanja Barang</span>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type-tabungan" value="tabungan" {{ old('type') === 'tabungan' ? 'checked' : '' }} required>
                                    <label class="btn btn-outline-success w-100 py-2 text-center" for="type-tabungan">
                                        <i class="bi bi-piggy-bank d-block fs-5 mb-1"></i>
                                        <span class="small fw-bold">Menabung Kas</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="date" class="form-label fw-semibold">Tanggal & Waktu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', date('Y-m-d')) }}" required>
                                <input type="text" name="time" id="time" class="form-control" style="max-width: 90px;" value="{{ old('time', date('H:i')) }}" placeholder="HH:MM">
                            </div>
                            @error('date')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Cabang --}}
                    @if($userBranch)
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Cabang Penugasan</label>
                            <div class="p-2 bg-light rounded border d-flex align-items-center">
                                <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                <div>
                                    <strong>{{ $userBranch->name }}</strong>
                                    <small class="text-muted d-block">{{ $userBranch->address }}</small>
                                </div>
                            </div>
                            <input type="hidden" name="branch_id" value="{{ $userBranch->id }}">
                        </div>
                    @else
                        <div class="mb-3">
                            <label for="branch_id" class="form-label fw-semibold">Cabang Warung</label>
                            <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                                <option value="">-- Pilih Cabang --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>
                                        {{ $b->name }} ({{ $b->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label for="name" class="form-label fw-semibold">Nama Barang / Keperluan <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Beras 10kg / Gas 3kg / Kantong kresek" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-5">
                            <label for="category" class="form-label fw-semibold">Kategori</label>
                            <input type="text" name="category" id="category" class="form-control @error('category') is-invalid @enderror" list="category-list" value="{{ old('category') }}" placeholder="Pilih / ketik kategori">
                            <datalist id="category-list">
                                <option value="Bahan Baku">
                                <option value="Gas & Energi">
                                <option value="Kemasan & Plastik">
                                <option value="Kebersihan & Sanitasi">
                                <option value="Tabungan Harian">
                                <option value="Operasional Warung">
                            </datalist>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label fw-semibold">Nominal Uang (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold">Rp</span>
                            <input type="number" name="amount" id="amount" class="form-control form-control-lg fw-bold text-dark @error('amount') is-invalid @enderror" value="{{ old('amount') }}" placeholder="Contoh: 35000" min="1" step="500" required>
                        </div>
                        @error('amount')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label fw-semibold">Catatan / Tempat Pembelian (Opsional)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Contoh: Beli di Toko Sembako Makmur / Agen Gas">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                            <i class="bi bi-save me-1"></i> Simpan Catatan Kas
                        </button>
                        <a href="{{ route('operational-cash.index') }}" class="btn btn-light border px-3">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typePengeluaran = document.getElementById('type-pengeluaran');
    const typeTabungan = document.getElementById('type-tabungan');
    const nameInput = document.getElementById('name');
    const categoryInput = document.getElementById('category');
    const amountInput = document.getElementById('amount');

    document.querySelectorAll('.quick-chip').forEach(btn => {
        btn.addEventListener('click', function () {
            const type = this.dataset.type;
            const cat = this.dataset.category;
            const name = this.dataset.name;

            if (type === 'tabungan') {
                typeTabungan.checked = true;
            } else {
                typePengeluaran.checked = true;
            }

            nameInput.value = name;
            categoryInput.value = cat;
            amountInput.focus();
        });
    });
});
</script>
@endpush
