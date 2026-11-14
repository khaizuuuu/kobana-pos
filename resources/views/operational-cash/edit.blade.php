@extends('layouts.app')

@section('title', 'Edit Catatan Kas')
@section('page-title', 'Edit Catatan Kas Operasional')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-primary"></i> Edit Catatan: {{ $operationalCash->name }}
                </h6>
                <a href="{{ route('operational-cash.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('operational-cash.update', $operationalCash) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Catatan Kas <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type-pengeluaran" value="pengeluaran" {{ old('type', $operationalCash->type) === 'pengeluaran' ? 'checked' : '' }} required>
                                    <label class="btn btn-outline-danger w-100 py-2 text-center" for="type-pengeluaran">
                                        <i class="bi bi-cart-dash d-block fs-5 mb-1"></i>
                                        <span class="small fw-bold">Belanja Barang</span>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type-tabungan" value="tabungan" {{ old('type', $operationalCash->type) === 'tabungan' ? 'checked' : '' }} required>
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
                                <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $operationalCash->date->format('Y-m-d')) }}" required>
                                <input type="text" name="time" id="time" class="form-control" style="max-width: 90px;" value="{{ old('time', $operationalCash->time ? substr($operationalCash->time, 0, 5) : '') }}" placeholder="HH:MM">
                            </div>
                            @error('date')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @if(auth()->user()->isAdmin())
                        <div class="mb-3">
                            <label for="branch_id" class="form-label fw-semibold">Cabang Warung</label>
                            <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                                <option value="">-- Tanpa Cabang (Pusat / Global) --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ old('branch_id', $operationalCash->branch_id) == $b->id ? 'selected' : '' }}>
                                        {{ $b->name }} ({{ $b->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @else
                        @if($operationalCash->branch)
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Cabang</label>
                            <div class="p-2 bg-light rounded border text-muted">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $operationalCash->branch->name }}
                            </div>
                        </div>
                        @endif
                    @endif

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label for="name" class="form-label fw-semibold">Nama Barang / Keperluan <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $operationalCash->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-5">
                            <label for="category" class="form-label fw-semibold">Kategori</label>
                            <input type="text" name="category" id="category" class="form-control @error('category') is-invalid @enderror" list="category-list" value="{{ old('category', $operationalCash->category) }}">
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
                            <input type="number" name="amount" id="amount" class="form-control form-control-lg fw-bold text-dark @error('amount') is-invalid @enderror" value="{{ old('amount', $operationalCash->amount) }}" min="1" step="500" required>
                        </div>
                        @error('amount')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label fw-semibold">Catatan / Tempat Pembelian (Opsional)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes', $operationalCash->notes) }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Perbarui Catatan Kas
                        </button>
                        <a href="{{ route('operational-cash.index') }}" class="btn btn-light border px-3">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
