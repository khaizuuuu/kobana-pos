@extends('layouts.app')

@section('title', 'Ganti Password')
@section('page-title', 'Pengaturan Profil & Password')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-shield-lock-fill text-primary"></i> Ganti Password Akun
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('profile.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Nama Pengguna</label>
                        <input type="text" class="form-control bg-light" value="{{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})" readonly disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Alamat Email</label>
                        <input type="email" class="form-control bg-light" value="{{ auth()->user()->email }}" readonly disabled>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-semibold">Password Saat Ini</label>
                        <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password Baru</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 6 karakter" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Password Baru
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
